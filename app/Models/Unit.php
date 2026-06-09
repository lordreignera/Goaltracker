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

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}
