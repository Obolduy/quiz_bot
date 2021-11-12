<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;

class ChangeQuizNameController extends Controller
{
    public function changeQuizName($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $quiz = Quizes::find(Redis::hget($id, 'quiz_id'));

        if (mb_strtolower($message_text, 'UTF-8') == 'название') {
            $bot->sendMessage($id, "Текущее название викторины: {$quiz->name}. Введите новое название.");
        } else {
            $quiz->name = $message_text;
            $quiz->save();

            Redis::hmset($id, 'status_id', '1');
            Redis::hdel($id, 'quiz_id');

            $bot->sendMessage($id, "Название викторины успешно изменено!");
        }
    }
}
