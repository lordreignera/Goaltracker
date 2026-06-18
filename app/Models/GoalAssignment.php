<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalAssignment extends Model
{
    protected $fillable = ['goal_id', 'department_id', 'section_id', 'unit_id', 'user_id'];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
