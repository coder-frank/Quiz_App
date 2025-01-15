<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerResponse extends Model
{
    use HasFactory;
    protected $fillable = ['department_id', 'question_id', 'is_correct', 'points'];
}
