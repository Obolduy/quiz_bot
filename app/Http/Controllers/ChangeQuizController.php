<?php

namespace App\Http\Controllers;

use App\Models\{Questions, Quizes};
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
                
                $this->changeQuestion($update, $bot);

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
            Redis::hdel($id, 'quiz_id');

            $bot->sendMessage($id, "Название викторины успешно изменено!");
        }
    }

    public function changeQuestion($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $questions = Questions::where('quiz_id', Redis::hget($id, 'quiz_id'))->get();

        foreach ($questions as $question) {
            $questions_list[] = $question->question;
        }

        if (mb_strtolower($message_text, 'UTF-8') == 'вопрос') {
            $keyboard = new ReplyKeyboardMarkup(
                [
                    $questions_list
                ], 
                true
            );
    
            $bot->sendMessage($id, 'Выберите, какой вопрос Вы хотите отредактировать', null, false, null, $keyboard);
        } else if (in_array($message_text, $questions_list)) {
            foreach ($questions as $question) {
                if ($question->question == $message_text) {
                    Redis::hmset($id, 'question_id', $question->id);

                    break;
                }
            }

            Redis::hmset($id, 'status_id', '16');
            $bot->sendMessage($id, "Введите новое название");
        } else {
            if (Redis::hget($id, 'status_id') == '16') {
                $question = Questions::find(Redis::hget($id, 'question_id'));

                $message_array = str_split($message_text);

                if (!in_array('?', $message_array)) {
                    array_push($message_array, '?');
                }

                $message_text = implode('', $message_array);

                $question->question = $message_text;
                $question->save();

                Redis::hmset($id, 'status_id', '1');
                Redis::hdel($id, 'quiz_id');
                Redis::hdel($id, 'question_id');

                $bot->sendMessage($id, "Вопрос успешно изменен, не забудьте обновить ответы к нему!");
            } else {
                $bot->sendMessage($id, "Неправильное название вопроса");
            }
        }
    }
}