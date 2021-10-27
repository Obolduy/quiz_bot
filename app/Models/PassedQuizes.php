<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassedQuizes extends Model
{
    use HasFactory;

    protected $fillable = [
        'passed_quiz_id',
        'user_id',
        'total_score'
    ];

    public $timestamps = false;
}
