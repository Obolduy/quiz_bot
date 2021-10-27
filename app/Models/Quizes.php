<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quizes extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'name'
    ];

    public $timestamps = false;
}
