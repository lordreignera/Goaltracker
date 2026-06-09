<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalObjective extends Model
{
    protected $fillable = ['goal_id', 'title', 'description', 'weight', 'status', 'due_at'];

    protected $casts = [
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
}
