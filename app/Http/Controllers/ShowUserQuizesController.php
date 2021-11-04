<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ShowUserQuizesController extends Controller
{
    public function showUserQuizes($message, $bot)
    {
        $id = $message->getChat()->getId();

        $quizes = Quizes::where('creator_id', $id)->get();

        Redis::hmset($id, 'status_id', '2');

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $quiz_list
            ], 
            true
        );

        $bot->sendMessage($id, 'Выберите викторину', null, false, null, $keyboard);
    }
}
