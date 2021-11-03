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

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        $id = 810293946;

        // var_dump(Redis::hgetall($id."_create_correct_answers_question")); die();
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

                $questions[$i] = $question;
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

            foreach ($questions as $question) {
                foreach ($all_answers as $answer) {
                    if ($answer->question_id == $question->id) {
                        foreach (Redis::hgetall($id."_create_correct_answers_question") as $key => $value) {
                            if ($question->question == $key && $answer->answer == $value) {
                                CorrectAnswers::create([
                                    'question_id' => $answer->question_id,
                                    'answer_id' => $answer->id
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }    
}