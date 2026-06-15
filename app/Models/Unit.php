<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['department_id', 'name', 'code', 'description'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function goalAssignments()
    {
        return $this->hasMany(GoalAssignment::class);
    }

    public function goals()
    {
        return $this->belongsToMany(Goal::class, 'goal_assignments', 'unit_id', 'goal_id')
            ->wherePivotNotNull('unit_id')
            ->distinct()
            ->withTimestamps();
    }
}
