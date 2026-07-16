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
            && app(GoalAccessService::class)->canSubmitDailyReport($this->user(), $objective->goal);
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
                $objective = $this->route('objective');

                if (! $objective instanceof GoalObjective || ! $this->filled('report_date')) {
                    return;
                }

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
                    ->where('user_id', $this->user()->id)
                    ->whereDate('report_period_start', $periodStart->toDateString())
                    ->exists();

                if ($alreadySubmitted) {
                    $validator->errors()->add(
                        'report_date',
                        'You have already submitted a report for this sub-goal reporting period. Edit the existing submission instead.'
                    );
                }
            },
        ];
    }

    public function preparedUpdateData(): array
    {
        $data = $this->safe()->except('evidence_file');
        $objective = $this->route('objective');
        $reportDate = $this->date('report_date');
        [$periodStart, $periodEnd] = $objective->reportingPeriodFor($reportDate);

        $data['is_progress_update'] = $this->boolean('is_progress_update');
        $data['report_period_start'] = $periodStart->toDateString();
        $data['report_period_end'] = $periodEnd->toDateString();

        if (! $data['is_progress_update']) {
            $data['achievement_percentage'] = null;
        }

        if ($this->hasFile('evidence_file')) {
            $file = $this->file('evidence_file');

            $data['evidence_path'] = $file->store('weekly-update-evidence', 'public');
            $data['evidence_original_name'] = $file->getClientOriginalName();
        }

        $data['submitted_at'] = now();

        return $data;
    }
}
