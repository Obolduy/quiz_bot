<?php

namespace App\Http\Controllers;

use App\Models\Questions;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ChangeQuestionController extends Controller
{
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
            $this->sendQuestionsList($bot, $id, $questions_list);
        }

        if (Redis::hget($id, 'status_id') == 16) {
            $this->setNewQuestionTitle($message_text, Questions::find(Redis::hget($id, 'question_id')));

            Redis::hmset($id, 'status_id', '1');
            Redis::hdel($id, 'quiz_id');
            Redis::hdel($id, 'question_id');

            $bot->sendMessage($id, "Вопрос успешно изменен, не забудьте обновить ответы к нему!");
        } else {
            $this->rememberSelectedQuestionId($bot, $message_text, $id, $questions, $questions_list);
        }
    }

    private function sendQuestionsList($bot, $user_id, array $questions_list): void
    {
        $keyboard = new ReplyKeyboardMarkup(
            [
                $questions_list
            ], 
            true
        );

        $bot->sendMessage($user_id, 'Выберите, какой вопрос Вы хотите отредактировать', null, false, null, $keyboard);
    }

    private function rememberSelectedQuestionId($bot, $message_text, $user_id, $questions, $questions_list)
    {
        if (in_array($message_text, $questions_list)) {
            foreach ($questions as $question) {
                if ($question->question == $message_text) {
                    Redis::hmset($user_id, 'question_id', $question->id);

                    break;
                }
            }

            Redis::hmset($user_id, 'status_id', '16');
            $bot->sendMessage($user_id, "Введите новый вопрос");
        } else {
            if (mb_strtolower($message_text, 'UTF-8') !== 'вопрос') {
                $bot->sendMessage($user_id, "Вопрос некорректен");
            }
        }
    }

    private function setNewQuestionTitle(string $message_text, $question): void
    {
        $message_array = str_split($message_text);

        if ($message_array[count($message_array) - 1] !== '?') {
            array_push($message_array, '?');
        }

        $message_text = implode('', $message_array);

        $question->question = $message_text;
        $question->save();
    }
}
