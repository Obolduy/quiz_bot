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
        $results = Quizes::select('passed_quizes.*', 'quizes.name')
                    ->leftJoin('passed_quizes', 'quizes.id', '=', 'passed_quizes.passed_quiz_id')
                    ->where('passed_quizes.user_id', 810293946)
                    ->orderBy('passed_quizes.total_score', 'desc')
                    ->distinct()
                    ->get();

        $results_message = '';
        $quiz_id = [];

        foreach ($results as $result) {
            if (in_array($result->passed_quiz_id, $quiz_id)) {
                continue;
            }

            $questions_count = Questions::where('quiz_id', $result->passed_quiz_id)->count('id');
            $quiz_id[] = $result->passed_quiz_id;
            $results_message .= "Название викторины: {$result->name} <br> Ваше последнее число набранных баллов: {$result->total_score} из $questions_count <br>";
        }

        echo $results_message;
    }  
}