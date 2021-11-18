<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class CreateQuizNameController extends Controller
{
    /**
     * Sets quiz name by user's message
     * @param Update
     * @param Client
     * @return void
     */

    public function createQuizName(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        if (Quizes::where('name', $message_text)->first()) {
            $bot->sendMessage($id, 'Данное название уже используется, пожалуйста, придумайте другое');
        } else {
            Redis::hmset($id, 'status_id', '6');
            Redis::hset($id."_create_quiz", 'quiz_name', $message_text);

            $bot->sendMessage($id, 'Отлично, теперь пора придумывать вопросы! Введите и отправьте свой вопрос. Вы можете сопроводить вопрос изображением, загрузив его вместе с текстом.');
        }
    }
}
