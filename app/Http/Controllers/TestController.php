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
        $message_text = 'Готов';

        $questions = Redis::hgetall($id."_create_quiz"); // получаем вопросы

            for ($i = 1; $i < count($questions); $i++) {
                // пишем в массив ответы ко всем вопросам и их порядковые номера, как номер ответа=>ответ
                $answers[$i] = Redis::hgetall($id."_create_answers_question_$i");
            }

        $message_check = (int)$message_text;
        var_dump($message_check);
        var_dump(is_int((int)$message_text));
        $message_array = str_split(str_replace([' ', ',', '.'], '', $message_text));

        if (count($answers) == count($message_array)) {
            for ($i = 0; $i <= count($message_array); $i++) {
                $number = $i + 1;
                if ($number <= count($message_array)) {
                    Redis::hset($id."_create_correct_answers_question", "question_$number", $message_array[$i]);
                }
            }

            var_dump(Redis::hgetall($id."_create_correct_answers_question"));
        } else {
            $variables = '';

            foreach ($answers as $number => $answer) {
                $variables .= $questions["question_$number"] . "\n";
                for ($i = 1; $i <= count($answer); $i++) {
                    $variables .= "$i. ". $answer["answer_$i"]."\n"; // записываем ответы как номер. ответ
                }
            }

            echo $variables;
        }
    }    
}