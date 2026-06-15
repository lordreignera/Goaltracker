<?php

namespace App\Models;

use App\Services\GoalAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'quarter_id',
        'created_by',
        'owner_id',
        'title',
        'specific',
        'measurable',
        'achievable',
        'relevant',
        'time_bound',
        'key_action_steps',
        'primary_metric',
        'deadline',
        'level',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'deadline' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'key_action_steps' => 'array',
    ];

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignments()
    {
        return $this->hasMany(GoalAssignment::class);
    }

    public function assignedDepartments()
    {
        return $this->belongsToMany(Department::class, 'goal_assignments', 'goal_id', 'department_id')
            ->wherePivotNotNull('department_id')
            ->distinct()
            ->withTimestamps();
    }

    public function assignedUnits()
    {
        return $this->belongsToMany(Unit::class, 'goal_assignments', 'goal_id', 'unit_id')
            ->wherePivotNotNull('unit_id')
            ->distinct()
            ->withTimestamps();
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'goal_assignments', 'goal_id', 'user_id')
            ->wherePivotNotNull('user_id')
            ->distinct()
            ->withTimestamps();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function objectives()
    {
        return $this->hasMany(GoalObjective::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return app(GoalAccessService::class)->scopeVisibleGoals($query, $user);
    }

    public function progress(): int
    {
        $this->loadMissing(['quarter', 'objectives.weeklyUpdates']);

        return (int) round($this->objectives->sum(fn (GoalObjective $objective) => $objective->progressContribution()));
    }
}
