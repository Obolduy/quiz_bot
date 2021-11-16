<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPictures extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'picture'
    ];

    public $timestamps = false;
}