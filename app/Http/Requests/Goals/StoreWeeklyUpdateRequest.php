<?php

namespace App\Http\Requests\Goals;

use App\Models\GoalObjective;
use App\Services\GoalAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWeeklyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $objective = $this->route('objective');

        return $this->user()
            && $objective instanceof GoalObjective
            && app(GoalAccessService::class)->canViewGoal($this->user(), $objective->goal);
    }

    public function rules(): array
    {
        return [
            'week_number' => ['required', 'integer', 'min:1', 'max:13'],
            'week_starting' => ['required', 'date'],
            'progress_summary' => ['required', 'string'],
            'achievements' => ['nullable', 'array'],
            'achievements.*' => ['nullable', 'string', 'max:1000'],
            'challenges' => ['nullable', 'array'],
            'challenges.*' => ['nullable', 'string', 'max:1000'],
            'next_actions' => ['nullable', 'array'],
            'next_actions.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $objective = $this->route('objective');

                if (! $objective instanceof GoalObjective || ! $this->filled('week_starting')) {
                    return;
                }

                $goal = $objective->goal()->with('quarter')->first();
                $quarter = $goal?->quarter;
                $weekStarting = $this->date('week_starting');

                if (! $quarter || ! $weekStarting) {
                    return;
                }

                $weekNumber = (int) $this->input('week_number');
                [$firstAllowedDate, $lastAllowedDate] = $objective->reportingDateRange();
                $expectedWeekStarting = $firstAllowedDate->copy()->addDays(($weekNumber - 1) * 7);

                if ($expectedWeekStarting->gt($lastAllowedDate)) {
                    $validator->errors()->add(
                        'week_number',
                        'Selected week is outside the objective reporting period.'
                    );
                }

                if (! $weekStarting->isSameDay($expectedWeekStarting)) {
                    $validator->errors()->add(
                        'week_starting',
                        "Week {$weekNumber} must start on {$expectedWeekStarting->toFormattedDateString()}."
                    );
                }

                if ($weekStarting->lt($firstAllowedDate) || $weekStarting->gt($lastAllowedDate)) {
                    $validator->errors()->add(
                        'week_starting',
                        "Weekly report date must be between {$firstAllowedDate->toFormattedDateString()} and {$lastAllowedDate->toFormattedDateString()}."
                    );
                }

                $alreadySubmitted = $objective->weeklyUpdates()
                    ->where('user_id', $this->user()->id)
                    ->whereDate('week_starting', $weekStarting->toDateString())
                    ->exists();

                if ($alreadySubmitted) {
                    $validator->errors()->add(
                        'week_starting',
                        'You have already submitted a weekly report for this objective on this date. Edit the existing submission instead.'
                    );
                }
            },
        ];
    }

    public function preparedUpdateData(): array
    {
        $data = $this->validated();

        foreach (['achievements', 'challenges', 'next_actions'] as $field) {
            $data[$field] = collect($data[$field] ?? [])
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->implode("\n");
        }

        $data['submitted_at'] = now();

        return $data;
    }
}
