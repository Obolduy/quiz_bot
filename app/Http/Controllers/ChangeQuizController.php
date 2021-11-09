<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
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

        // в кейсах пускай методы принмают $update и пускай там идет отсев что если текст равен кейсу 
        // то это типа начало изменения а если наоборот то само изменение как в добавлении короче
        switch ($message_text) {
            case "название":
                Redis::hmset($id, 'status_id', '14');

                $this->changeQuizName($update, $bot);

                break;
            case "вопрос":
                Redis::hmset($id, 'status_id', '15');
                break;
            case "ответ":
                Redis::hmset($id, 'status_id', '16');
                break;
            default:
                $bot->sendMessage($id, 'Команда не распознана, пожалуйста, уточните, что Вы хотите.');
        }
    }

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
            $bot->sendMessage($id, "Название викторины успешно изменено!");
        }
    }
}
