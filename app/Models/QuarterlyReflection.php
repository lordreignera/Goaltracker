<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarterlyReflection extends Model
{
    protected $fillable = [
        'quarter_id',
        'user_id',
        'department_id',
        'section_id',
        'unit_id',
        'goals_completed',
        'goals_partially_completed',
        'key_wins',
        'challenges',
        'lessons_learned',
        'next_quarter_focus',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
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
}
