<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\CreateQuizController;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class CreateQuizCorrectAnswersController extends Controller
{
    /**
     * Sends answers list and signs correct by user's message
     * @param Update
     * @param Client
     * @return void
     */

    public function createQuizCorrectAnswers(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $questions = Redis::hgetall($id."_create_quiz"); // получаем вопросы

        for ($i = 1; $i < count($questions); $i++) {
            // пишем в массив ответы ко всем вопросам и их порядковые номера, как номер ответа=>ответ
            $answers[$i] = Redis::hgetall($id."_create_answers_question_$i");
        }

        $message_array = str_split(str_replace([' ', ',', '.'], '', (int)$message_text));

        // если пользователь написал "готов", отправляем ему список вопросов с ответами,
        // если сообщение иное - бот ожидает, что это номера правильных ответов
        if (count($answers) == count($message_array) && 'готов' !== mb_strtolower($message_text, 'UTF-8')) {
            for ($i = 0; $i <= count($message_array); $i++) {
                $number = $i + 1;
                if ($number <= count($message_array)) {
                    $question_name = Redis::hget($id."_create_quiz", "question_$number");
                    $question_answer = Redis::hget($id."_create_answers_question_$number", "answer_{$message_array[$i]}");
                    Redis::hset($id."_create_correct_answers_question", $question_name, $question_answer);
                }
            }

            (new CreateQuizController)->createQuizDone($id);

            $bot->sendMessage($id, "Поздравляем! Ваша викторина успешно создана,
                Вы можете посмотреть все созданные викторины с помощью команды /my_quizes");

            (new MainController)->mainPage($bot, $message->getChat()->getId());
        } else {
            $variables = '';

            foreach ($answers as $number => $answer) {
                $variables .= $questions["question_$number"] . "\n";
                for ($i = 1; $i <= count($answer); $i++) {
                    $variables .= "$i. ". $answer["answer_$i"]."\n"; // записываем ответы как номер. ответ
                }
            }
            $bot->sendMessage($id, $variables);
        }
    }
}