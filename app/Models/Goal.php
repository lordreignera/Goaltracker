<?php

namespace App\Models;

use App\Services\GoalAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'quarter_id',
        'department_id',
        'unit_id',
        'owner_id',
        'title',
        'description',
        'level',
        'status',
    ];

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedDepartments()
    {
        return $this->belongsToMany(Department::class, 'goal_department')->withTimestamps();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function assignedUnits()
    {
        return $this->belongsToMany(Unit::class, 'goal_unit')->withTimestamps();
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
