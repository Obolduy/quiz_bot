<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ShowQuizListController extends Controller
{
    public function showQuizes($message, $bot)
    {
        Redis::hmset($message->getChat()->getId(), 'status_id', '2');

        $quizes = Quizes::all();

        $quiz_list = [];

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $quiz_list
            ], true);

        $bot->sendMessage($message->getChat()->getId(),
            'Выберите викторину', null, false, null, $keyboard);
    }
}