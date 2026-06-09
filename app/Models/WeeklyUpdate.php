<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyUpdate extends Model
{
    protected $fillable = [
        'goal_objective_id',
        'user_id',
        'week_number',
        'week_starting',
        'progress_summary',
        'achievements',
        'challenges',
        'next_actions',
        'percentage_estimate',
        'status',
    ];

    protected $casts = [
        'week_starting' => 'date',
    ];

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
}
