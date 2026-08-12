<?php

namespace App\Http\Requests\Goals;

use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
            && app(GoalAccessService::class)->canSubmitDailyReport($this->user(), $weeklyUpdate->objective->goal);
    }

    public function rules(): array
    {
        return [
            'report_date' => ['required', 'date'],
            'is_progress_update' => ['nullable', 'boolean'],
            'achievement_percentage' => ['required_if:is_progress_update,1', 'nullable', 'integer', 'min:0', 'max:100'],
            'achievement_summary' => ['required', 'string', 'max:3000'],
            'challenges' => ['nullable', 'string', 'max:3000'],
            'action_points' => ['nullable', 'string', 'max:3000'],
            'evidence_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $weeklyUpdate = $this->route('weeklyUpdate');

                if (! $weeklyUpdate instanceof WeeklyUpdate || ! $this->filled('report_date')) {
                    return;
                }

                $objective = $weeklyUpdate->objective;
                $goal = $objective->goal()->with('quarter')->first();
                $quarter = $goal?->quarter;
                $reportDate = $this->date('report_date');

                if (! $quarter || ! $reportDate) {
                    return;
                }

                [$firstAllowedDate, $lastAllowedDate] = $objective->reportingDateRange();

                if ($reportDate->lt($firstAllowedDate) || $reportDate->gt($lastAllowedDate)) {
                    $validator->errors()->add(
                        'report_date',
                        "Report date must be between {$firstAllowedDate->toFormattedDateString()} and {$lastAllowedDate->toFormattedDateString()}."
                    );
                }

                [$periodStart] = $objective->reportingPeriodFor($reportDate);

                $alreadySubmitted = $objective->weeklyUpdates()
                    ->whereKeyNot($weeklyUpdate->id)
                    ->where('user_id', $this->user()->id)
                    ->whereDate('report_period_start', $periodStart->toDateString())
                    ->exists();

                if ($alreadySubmitted) {
                    $validator->errors()->add(
                        'report_date',
                        'You already have another report for this sub-goal reporting period.'
                    );
                }
            },
        ];
    }

    public function preparedUpdateData(): array
    {
        $data = $this->safe()->except('evidence_file');
        $weeklyUpdate = $this->route('weeklyUpdate');
        $reportDate = $this->date('report_date');
        [$periodStart, $periodEnd] = $weeklyUpdate->objective->reportingPeriodFor($reportDate);

        $data['is_progress_update'] = $this->boolean('is_progress_update');
        $data['report_period_start'] = $periodStart->toDateString();
        $data['report_period_end'] = $periodEnd->toDateString();

        if (! $data['is_progress_update']) {
            $data['achievement_percentage'] = null;
        }

        if ($this->hasFile('evidence_file')) {
            $file = $this->file('evidence_file');
            $path = Storage::disk('public')->putFile('weekly-update-evidence', $file);

            if (! is_string($path)) {
                throw ValidationException::withMessages([
                    'evidence_file' => 'The evidence file could not be saved. Please try uploading it again.',
                ]);
            }

            $data['evidence_path'] = $path;
            $data['evidence_original_name'] = $file->getClientOriginalName();
        }

        $data['submitted_at'] = now();

        return $data;
    }
}
