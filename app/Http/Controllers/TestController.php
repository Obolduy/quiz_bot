<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\CurrentUserQuiz;
use App\Models\PassedQuizes;
use App\Models\QuestionPictures;
use App\Models\Questions;
use App\Models\Quizes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        $id = 810293946;
        
        $current_user_quiz = CurrentUserQuiz::where('user_id', $id)->where('quiz_id', 5)->get();


        var_dump($current_user_quiz);

    }  
}