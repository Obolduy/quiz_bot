<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\CurrentUserQuiz;
use App\Models\PassedQuizes;
use App\Models\Questions;
use App\Models\Quizes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        $id = 810293946;

        Redis::hmset($id, 'status_id', '19');

        $questions = Questions::where('quiz_id', Redis::hgset($id, 'quiz_id'))->get();

        $questions_list = [];
        foreach ($questions as $question) {
            $questions_list[] = $question->question;
        }

        var_dump($questions_list);
    }  
}