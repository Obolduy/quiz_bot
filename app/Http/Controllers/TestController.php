<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\CurrentUserQuiz;
use App\Models\PassedQuizes;
use App\Models\Quizes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        $correct_answers = Redis::hgetall(self::$id."_create_correct_answers_question");

        $questions = Redis::hgetall(self::$id."_create_quiz");

        $count = 1;
        foreach ($questions as $key => $value) {
            if ($key != 'quiz_name') {
                $answers[$count] = Redis::hgetall(self::$id."_create_answers_question_$count");
                $count++;
            }
        }

        foreach ($answers as $number => $answer) {
            if ((count($correct_answers) + 1) == $number) {
                if ($request->isMethod('GET')) {
                    foreach ($answer as $key => $variable) {
                        echo $variable . "\n";
                    }
                    
                    return view('welcome');
                }

                Redis::hset(self::$id."_create_correct_answers_question", "answer_$number", $request->text);
            }
        }
    }
}