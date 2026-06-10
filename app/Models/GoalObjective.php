<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class GoalObjective extends Model
{
    protected $fillable = ['goal_id', 'title', 'description', 'weight', 'status', 'starts_at', 'due_at'];

    protected $casts = [
        'starts_at' => 'date',
        'due_at' => 'date',
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

    public function isApprovedComplete(): bool
    {
        return $this->approvedReportingWeeksCount() >= $this->totalReportingWeeks();
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

        return min(13, (int) floor($firstReportingDate->diffInDays($lastReportingDate) / 7) + 1);
    }

    public function approvedReportingWeeksCount(): int
    {
        $this->loadMissing('goal.quarter');

        $quarter = $this->goal?->quarter;

        if (! $quarter?->starts_at || ! $quarter?->ends_at) {
            return 0;
        }

        [$firstReportingDate, $lastReportingDate] = $this->reportingDateRange();

        if ($this->relationLoaded('weeklyUpdates')) {
            return $this->weeklyUpdates
                ->where('status', 'approved')
                ->filter(fn (WeeklyUpdate $update) => $update->week_starting
                    && $update->week_starting->betweenIncluded($firstReportingDate, $lastReportingDate))
                ->pluck('week_starting')
                ->map(fn (Carbon $date) => $date->toDateString())
                ->unique()
                ->count();
        }

        return $this->approvedWeeklyUpdates()
            ->whereBetween('week_starting', [$firstReportingDate, $lastReportingDate])
            ->distinct('week_starting')
            ->count('week_starting');
    }

    public function weeklyProgressPercent(): float
    {
        $totalWeeks = $this->totalReportingWeeks();

        if ($totalWeeks < 1) {
            return 0;
        }

        return min(100, ($this->approvedReportingWeeksCount() / $totalWeeks) * 100);
    }

    public function progressContribution(): float
    {
        return ($this->weight * $this->weeklyProgressPercent()) / 100;
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

    public function reportingWeekOptions(): array
    {
        [$firstReportingDate, $lastReportingDate] = $this->reportingDateRange();
        $options = [];
        $cursor = $firstReportingDate->copy();

        for ($week = 1; $week <= 13 && $cursor->lte($lastReportingDate); $week++) {
            $options[] = [
                'week' => $week,
                'date' => $cursor->toDateString(),
                'label' => 'Week '.$week.' - '.$cursor->format('M d, Y'),
            ];

            $cursor->addDays(7);
        }

        return $options;
    }
}
