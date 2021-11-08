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

        Redis::hmset($id, 'status_id', '11');

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $quiz_list
            ], 
            true
        );

        $bot->sendMessage($id, 'Выберите викторину, чтобы начать с ней взаимодействие', null, false, null, $keyboard);
    }

    public function selectUserQuiz($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $quiz = Quizes::where('name', $message_text)->first();
        Redis::hmset($id, "quiz_id", $quiz->id);

        $bot->sendMessage($id, "Вы выбрали Вашу викторину {$quiz->name}. Чтобы отредактировать викторину, напишите /quiz_change, чтобы удалить - /quiz_delete, а чтобы пройти её самостоятельно, напишите /quiz_start");
    }
}