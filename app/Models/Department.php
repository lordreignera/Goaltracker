<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
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
        return $this->belongsToMany(Goal::class, 'goal_assignments', 'department_id', 'goal_id')
            ->wherePivotNotNull('department_id')
            ->distinct()
            ->withTimestamps();
    }
}
