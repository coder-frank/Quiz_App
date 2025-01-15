<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];


    public function points()
    {
        return $this->hasMany(PlayerResponse::class, 'department_id');
    }

    public function totalPoints()
    {
        return $this->points()->sum('points');
    }
}
