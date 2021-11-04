<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;

class ShowQuizController extends Controller
{
    public function selectQuizByName($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $quiz = Quizes::where('name', $message_text)->first();

        if ($quiz) {
            Redis::hmset($id, 'status_id', '3');

            $bot->sendMessage($id, 'Да, все ок вот название викторины: '. $quiz->name);
        } else {
            $bot->sendMessage($id, 'Название викторины неверно!');
        }
    }
}