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
        if ($request->isMethod('GET')) {
            return view('welcome');
        }

        $questions_count = Redis::hgetall(self::$id."_create_quiz");

        $is_done = false;
        for ($i = 1; $i < count($questions_count); $i++) {
            $question = Redis::hgetall(self::$id."_create_answers_question_$i");

            $answer_count = count($question);

            if ($answer_count == 3 && $i == (count($questions_count) - 1)) {
                $is_done = true;
            }

            if ($answer_count < 4 || $question["answer_$answer_count"] == 'empty') {
                $answer_count++;
                Redis::hset(self::$id."_create_answers_question_$i", "answer_$answer_count", $request->text);

                break;
            }
        }

        if ($is_done) {
            Redis::hmset(self::$id, 'status_id', '8');
            var_dump('vse');
        }

        var_dump(Redis::hgetall(self::$id."_create_answers_question_4"));
    }
}