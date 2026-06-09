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

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}
