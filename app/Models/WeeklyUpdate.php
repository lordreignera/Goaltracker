<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WeeklyUpdate extends Model
{
    protected $fillable = [
        'goal_objective_id',
        'user_id',
        'report_date',
        'report_period_start',
        'report_period_end',
        'is_progress_update',
        'achievement_percentage',
        'achievement_summary',
        'challenges',
        'action_points',
        'evidence_path',
        'evidence_original_name',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'report_period_start' => 'date',
        'report_period_end' => 'date',
        'is_progress_update' => 'boolean',
        'achievement_percentage' => 'integer',
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (WeeklyUpdate $weeklyUpdate) {
            if ($weeklyUpdate->achievement_percentage !== null && $weeklyUpdate->is_progress_update === null) {
                $weeklyUpdate->is_progress_update = true;
            }

            if (! $weeklyUpdate->report_date || ($weeklyUpdate->report_period_start && $weeklyUpdate->report_period_end)) {
                return;
            }

            $objective = $weeklyUpdate->objective;

            if (! $objective) {
                return;
            }

            [$periodStart, $periodEnd] = $objective->reportingPeriodFor(Carbon::parse($weeklyUpdate->report_date));

            $weeklyUpdate->report_period_start = $periodStart->toDateString();
            $weeklyUpdate->report_period_end = $periodEnd->toDateString();
        });
    }

    public function objective()
    {
        return $this->belongsTo(GoalObjective::class, 'goal_objective_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(SupervisorReview::class);
    }

    public function hasEvidence(): bool
    {
        return filled($this->evidence_path);
    }

    public function latestApprovedReview()
    {
        return $this->hasOne(SupervisorReview::class)
            ->where('decision', 'approved')
            ->latestOfMany();
    }

    public function verifiedAchievementPercent(): ?int
    {
        if ($this->relationLoaded('reviews')) {
            $review = $this->reviews
                ->where('decision', 'approved')
                ->whereNotNull('verified_percentage')
                ->sortByDesc('created_at')
                ->first();

            return $review?->verified_percentage;
        }

        return $this->latestApprovedReview?->verified_percentage;
    }
}
