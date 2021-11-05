<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizStars extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'votes_count',
        'stars_avg',
        'stars_count'
    ];

    public $timestamps = false;
}
