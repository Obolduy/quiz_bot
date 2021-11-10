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
        $answers = Answers::select('answers.id', 'answers.answer', 'questions.id AS question_id', 'questions.question')
                            ->leftJoin('questions', 'questions.id', '=', 'answers.question_id')
                            ->leftJoin('quizes', 'quizes.id', '=', 'questions.quiz_id')
                            ->where('quizes.id', 2)
                            ->get();

        // foreach ($answers as $answer) {
        //     echo $answer->id . '<br>';
        //     echo $answer->answer . '<br>';
        //     echo $answer->question_id . '<br>';
        //     echo $answer->question . '<br>';
        // }

        $message_text = '[вопрос 3 кстати?] ОТВЕТ1';

        $matches = [];
        preg_match('#\[(.+)\]#u', $message_text, $matches);

        foreach ($answers as $answer) {
            if ($matches && $matches[1] !== $answer->question) {
                continue;
            }

            if ($matches && $matches[1] === $answer->question) {
                $message_text = trim(str_replace($matches[0], ' ', $message_text));
            }

            if ($answer->answer === $message_text) {
                echo $answer->answer .' '. $answer->id;
            }
        }
    }  
}