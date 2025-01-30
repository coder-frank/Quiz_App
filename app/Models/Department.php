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

    public function totalPointsForRound($round)
    {
        return PlayerResponse::where('department_id', $this->id)
            ->whereHas('question', function ($query) use ($round) {
                $query->where('round_number', $round);
            })
            ->sum('points');
    }
}
