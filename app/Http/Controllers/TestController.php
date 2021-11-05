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
        $page = 1;

        $pageFrom = ($page * 5) - 5;
        $pageTo = 5;
        $quizes = Quizes::select('quizes.*', 'quiz_stars.stars_avg')
                ->offset($pageFrom)
                ->leftJoin('quiz_stars', 'quizes.id', '=', 'quiz_stars.quiz_id')
                ->orderBy('quiz_stars.stars_avg', 'desc')
                ->limit($pageTo)
                ->get();

        foreach ($quizes as $quiz) {
            echo $quiz->name . "Оценка: {$quiz->stars_avg} звезд . <br>";
        }
    }  
}