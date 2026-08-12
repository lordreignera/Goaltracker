<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalPillar extends Model
{
    protected $fillable = [
        'name',
        'annual_goal',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}
