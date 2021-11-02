<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\CurrentUserQuiz;
use App\Models\Questions;
use App\Models\Quizes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\VarDumper\VarDumper;

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        $id = static::$id;

        DB::transaction(function () use ($id) {
            $quiz = Quizes::create([
                'name' => Redis::hget($id."_create_quiz", 'quiz_name'),
                'creator_id' => $id
            ]);

            for ($i = 1; $i < count(Redis::hgetall($id."_create_quiz")); $i++) {
                $question = Questions::create([
                    'quiz_id' => $quiz->id,
                    'question' => Redis::hget($id."_create_quiz", "question_$i")
                ]);

                $answers_list[$question->id] = Redis::hgetall($id."_create_answers_question_$i");
            }
            
            foreach ($answers_list as $question_id => $answers) {
                foreach ($answers as $answer) {
                    $all_answers[] = Answers::create([
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                }
            }

            for ($i = 1; $i <= count(Redis::hgetall($id."_create_correct_answers_question")); $i++) {
                $correct_answer = Redis::hget($id."_create_correct_answers_question", "question_$i");
                $correct_answers_array[] = Redis::hget($id."_create_answers_question_$i", $correct_answer);
            }

            foreach ($correct_answers_array as $elem) {
                foreach ($all_answers as $answers_text) {
                    if ($answers_text->answer == $elem) {
                        CorrectAnswers::create([
                            'question_id' => $answers_text->question_id,
                            'answer_id' => $answers_text->id
                        ]);
                    }
                }
            }
        });

        for ($i = 1; $i < count(Redis::hgetall($id."_create_quiz")); $i++) {
            Redis::del($id."_create_answers_question_$i");
        }

        Redis::del($id."_create_quiz");
        Redis::del($id."_create_correct_answers_question");
    }
}