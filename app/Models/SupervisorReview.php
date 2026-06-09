<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorReview extends Model
{
    protected $fillable = ['weekly_update_id', 'supervisor_id', 'decision', 'comments'];

    public function weeklyUpdate()
    {
        return $this->belongsTo(WeeklyUpdate::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
