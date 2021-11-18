<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\{Message, ReplyKeyboardMarkup, Update};

class ChangeQuizController extends Controller
{
    /**
     * Sends keyboard with list of change options
     * @param Message
     * @param Client
     * @return void
     */

    public function changeQuizStart(Message $message, Client $bot): void
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

    /**
     * Takes user's message and switches it to change quiz name, question or answer
     * @param Update
     * @param Client
     * @return void
     */

    public function chooseWhatToChange(Update $update, Client $bot): void
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