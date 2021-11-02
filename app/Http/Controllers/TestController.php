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
        $correct_answers = Redis::hgetall(self::$id."_create_correct_answers_question"); // получаем правильные ответы, если они записаны

        $questions = Redis::hgetall(self::$id."_create_quiz"); // получаем вопросы

        $count = 1; // нумерация с единицы, т.к. нулевой элемент в массиве - название квиза
        foreach ($questions as $key => $value) {
            if ($key != 'quiz_name') {
                // пишем в массив ответы ко всем вопросам и их порядковые номера, как номер ответа=>ответ
                $answers[$count] = Redis::hgetall(self::$id."_create_answers_question_$count");
                $count++;
            }
        }

        $variables = '';
        foreach ($answers as $number => $answer) {
            /* количество элементов массива правильных ответов равняется числу уже "пройденных" циклом
            вопросов, потому прибавляем к этому числу единицу, дабы записать в новый элемент новый ответ */
            if ((count($correct_answers) + 1) == $number) {
                for ($i = 1; $i <= count($answer); $i++) {
                    $variables .= "$i. ". $answer["answer_$i"]."\n"; // записываем вопросы как номер. вопрос
                }

                if ($request->isMethod('GET')) {
                    var_dump($variables); // над выводом ответов подумай 
                    return view('welcome');
                } else {
                    Redis::hset(self::$id."_create_correct_answers_question", "question_$number", "answer_".$request->text);
                }
            }
        }
    }
}