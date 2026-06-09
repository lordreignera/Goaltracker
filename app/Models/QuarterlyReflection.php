<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarterlyReflection extends Model
{
    protected $fillable = ['user_id', 'quarter_id', 'key_wins', 'challenges', 'lessons_learned', 'next_quarter_focus'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
    }
}
