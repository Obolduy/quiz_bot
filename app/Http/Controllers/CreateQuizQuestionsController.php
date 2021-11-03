<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;

class CreateQuizQuestionsController extends Controller
{
    public function createQuizQuestions($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_array = str_split(trim(strip_tags($message->getText())));

        if (!in_array('?', $message_array)) {
            array_push($message_array, '?');
        }

        $question = implode('', $message_array);

        $question_number = count(Redis::hgetall($id."_create_quiz"));
        $question_number = ($question_number == 1) ? 1 : $question_number; // нумеровать вопросы с единицы

        Redis::hset($id."_create_quiz", "question_$question_number", $question);

        $bot->sendMessage($id, 'Вопрос добавлен! Введите следующий или, если хотите закончить ввод, напишите "/add_questions_stop"');
    }
}
