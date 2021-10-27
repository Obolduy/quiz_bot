<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectAnswers extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer_id'
    ];

    public $timestamps = false;
}
