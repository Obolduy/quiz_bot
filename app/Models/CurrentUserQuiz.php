<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentUserQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'passed_question_id',
        'passed_answer_id',
        'user_id'
    ];

    public $timestamps = false;
}
