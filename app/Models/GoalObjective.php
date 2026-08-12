<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class GoalObjective extends Model
{
    protected $fillable = [
        'goal_id',
        'title',
        'key_activities',
        'specific_output',
        'weight',
        'planned_weeks',
        'reporting_frequency',
        'status',
        'starts_at',
        'due_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'due_at' => 'date',
        'planned_weeks' => 'integer',
        'reporting_frequency' => 'array',
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function weeklyUpdates()
    {
        return $this->hasMany(WeeklyUpdate::class);
    }

    public function approvedWeeklyUpdates()
    {
        return $this->weeklyUpdates()->where('status', 'approved');
    }

    public function keyActivitiesList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->key_activities))
            ->map(fn ($activity) => trim($activity))
            ->filter()
            ->values()
            ->all();
    }

    public function reportingFrequencies(): array
    {
        $frequencies = $this->reporting_frequency;

        if (is_string($frequencies)) {
            $decoded = json_decode($frequencies, true);
            $frequencies = json_last_error() === JSON_ERROR_NONE ? $decoded : [$frequencies];
        }

        return collect($frequencies ?: ['weekly'])
            ->filter(fn ($frequency) => in_array($frequency, ['daily', 'weekly', 'monthly'], true))
            ->values()
            ->all() ?: ['weekly'];
    }

    public function isApprovedComplete(): bool
    {
        return $this->approvedAchievementPercent() >= 100;
    }

    public function totalReportingWeeks(): int
    {
        $this->loadMissing('goal.quarter');

        $quarter = $this->goal?->quarter;

        if (! $quarter?->starts_at || ! $quarter?->ends_at) {
            return 13;
        }

        [$firstReportingDate, $lastReportingDate] = $this->reportingDateRange();

        if ($lastReportingDate->lt($firstReportingDate)) {
            return 0;
        }

        return min($this->planned_weeks ?: 13, (int) floor($firstReportingDate->diffInDays($lastReportingDate) / 7) + 1);
    }

    public function approvedAchievementPercent(): int
    {
        if ($this->relationLoaded('weeklyUpdates')) {
            $latestApproved = $this->weeklyUpdates
                ->where('status', 'approved')
                ->filter(fn (WeeklyUpdate $update) => $update->verifiedAchievementPercent() !== null)
                ->sortByDesc('report_period_start')
                ->first();

            return (int) ($latestApproved?->verifiedAchievementPercent() ?? 0);
        }

        $latestApproved = $this->approvedWeeklyUpdates()
            ->with('latestApprovedReview')
            ->whereHas('latestApprovedReview', fn ($query) => $query->whereNotNull('verified_percentage'))
            ->orderByDesc('report_period_start')
            ->get()
            ->first();

        return (int) ($latestApproved?->verifiedAchievementPercent() ?? 0);
    }

    public function progressPercent(): int
    {
        return min(100, $this->approvedAchievementPercent());
    }

    public function progressContribution(): float
    {
        return ($this->weight * $this->progressPercent()) / 100;
    }

    public function reportingDateRange(): array
    {
        $this->loadMissing('goal.quarter');

        $quarter = $this->goal?->quarter;
        $quarterStart = $quarter?->starts_at ?? now();
        $quarterEnd = $quarter?->ends_at ?? $quarterStart->copy()->addDays(89);

        $firstReportingDate = $this->starts_at && $this->starts_at->gt($quarterStart)
            ? $this->starts_at
            : $quarterStart;

        $lastReportingDate = $this->due_at && $this->due_at->lt($quarterEnd)
            ? $this->due_at
            : $quarterEnd;

        return [$firstReportingDate, $lastReportingDate];
    }

    public function reportingPeriodFor(CarbonInterface $reportDate, ?string $frequency = null): array
    {
        [$firstReportingDate, $lastReportingDate] = $this->reportingDateRange();
        $reportDate = $reportDate->copy()->startOfDay();
        $frequency = $frequency ?: $this->reportingFrequencies()[0];

        if ($frequency === 'daily') {
            return [$reportDate, $reportDate];
        }

        if ($frequency === 'monthly') {
            return [
                $reportDate->copy()->startOfMonth()->max($firstReportingDate),
                $reportDate->copy()->endOfMonth()->min($lastReportingDate),
            ];
        }

        $daysFromStart = max(0, (int) $firstReportingDate->diffInDays($reportDate, false));
        $weekOffset = intdiv($daysFromStart, 7);
        $periodStart = $firstReportingDate->copy()->addDays($weekOffset * 7);

        return [
            $periodStart,
            $periodStart->copy()->addDays(6)->min($lastReportingDate),
        ];
    }

}
