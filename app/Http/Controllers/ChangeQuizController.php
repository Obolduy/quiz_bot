<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ChangeQuizController extends Controller
{
    public function changeQuizStart($message, $bot)
    {
        $id = $message->getChat()->getId();

        Redis::hmset($id, 'status_id', '13');

        $keyboard = new ReplyKeyboardMarkup(
            [
                ["Название", "Вопрос", "Ответ"]
            ], 
            true
        );

        $bot->sendMessage($id, 'Выберите, что Вы хотите отредактировать: название, вопрос или ответ?', null, false, null, $keyboard);
    }

    public function chooseWhatToChange($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags(mb_strtolower($message->getText(), 'UTF-8')));

        switch ($message_text) {
            case "название":
                Redis::hmset($id, 'status_id', '14');

                (new ChangeQuizNameController)->changeQuizName($update, $bot);

                break;
            case "вопрос":
                Redis::hmset($id, 'status_id', '15');
                
                (new ChangeQuestionController)->changeQuestion($update, $bot);

                break;
            case "ответ":
                Redis::hmset($id, 'status_id', '17');
                                
                (new ChangeAnswerController)->changeAnswerStart($update, $bot);

                break;
            default:
                $bot->sendMessage($id, 'Команда не распознана, пожалуйста, уточните, что Вы хотите.');
        }
    }
}