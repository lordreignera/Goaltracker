<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['department_id', 'name', 'code', 'description'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
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
        return $this->belongsToMany(Goal::class, 'goal_assignments', 'section_id', 'goal_id')
            ->wherePivotNotNull('section_id')
            ->distinct()
            ->withTimestamps();
    }
}
