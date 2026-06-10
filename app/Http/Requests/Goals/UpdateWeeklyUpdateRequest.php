<?php

namespace App\Http\Requests\Goals;

use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateWeeklyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $weeklyUpdate = $this->route('weeklyUpdate');

        return $this->user()
            && $weeklyUpdate instanceof WeeklyUpdate
            && $weeklyUpdate->user_id === $this->user()->id
            && $weeklyUpdate->status !== 'approved'
            && app(GoalAccessService::class)->canViewGoal($this->user(), $weeklyUpdate->objective->goal);
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
            'percentage_estimate' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $weeklyUpdate = $this->route('weeklyUpdate');

                if (! $weeklyUpdate instanceof WeeklyUpdate || ! $this->filled('week_starting')) {
                    return;
                }

                $objective = $weeklyUpdate->objective;
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
                    ->whereKeyNot($weeklyUpdate->id)
                    ->where('user_id', $this->user()->id)
                    ->whereDate('week_starting', $weekStarting->toDateString())
                    ->exists();

                if ($alreadySubmitted) {
                    $validator->errors()->add(
                        'week_starting',
                        'You already have another weekly report for this objective on this date.'
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

        $data['percentage_estimate'] = $data['percentage_estimate'] ?? 0;

        return $data;
    }
}
