<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\{Message, Update};

class CreateQuizAnswersController extends Controller
{
    /**
     * Sends first question and waits for the message that will be the first answer for it
     * @param Message
     * @param Client
     * @return void
     */

    public function createQuizAnswerStart(Message $message, Client $bot): void
    {
        $id = $message->getChat()->getId();
        Redis::hmset($id, 'status_id', '7');

        $questions = Redis::hgetall($id."_create_quiz");

        $count = 1; // нумерация с единицы
        foreach ($questions as $key => $value) {
            if ($key != 'quiz_name') { // создает столько таблиц, сколько вопросов
                Redis::hset($id."_create_answers_question_$count", "answer_1", 'empty');
                $count++;
            }
        }

        $bot->sendMessage($id,
            "Мы закончили с вопросами! Теперь пора придумывать ответы на них. 
            Введите ответ и отправьте его, после 4-го варианта Вы будете автоматически переведены 
            на следующий вопрос. Правильные ответы Вы сможете пометить чуть позже.
            Для начала, введите первый ответ на Ваш вопрос \"{$questions['question_1']}\"");
    }

    /**
     * Takes user message and adds it into DB as an answer
     * @param Update
     * @param Client
     * @return void
     */

    public function createQuizAnswers(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $questions_count = Redis::hgetall($id."_create_quiz"); // получаем все вопросы

        $is_done = false; // проверка, закончено ли добавление ответов
        for ($i = 1; $i < count($questions_count); $i++) { // нумерация, начиная с первого вопроса
            $question = Redis::hgetall($id."_create_answers_question_$i"); // получаем все ответы вопроса

            $answer_count = count($question);

            $question_text_number = ($answer_count !== 3) ? $i : $i + 1;
            $question_text = Redis::hget($id."_create_quiz", "question_$question_text_number"); // получаем текст вопроса

            // если это последний ответ последнего вопроса, добавление ответов закончено
            if ($answer_count == 3 && $i == (count($questions_count) - 1)) {
                $is_done = true;
            }

            if ($question["answer_1"] == 'empty') { // т.к первый ответ всегда 'empty', перезаписываем его
                Redis::hset($id."_create_answers_question_$i", "answer_1", $message_text);

                break;
            }

            if ($answer_count < 4) { // количество ответов всегда 4
                $answer_count++;
                Redis::hset($id."_create_answers_question_$i", "answer_$answer_count", $message_text);

                break;
            }
        }

        if ($is_done) {
            Redis::hmset($id, 'status_id', '8');
            $bot->sendMessage($id,
                "Мы закончили с ответами! Самое время выбрать правильные ответы!
                    Сейчас Вам по порядку будут отправлены ответы на вопросы, Ваша задача - отметить правильный.
                        Пожалуйста, напишите номера правильных вариантов ответа одним сообщением.
                            Напишите 'Готов', чтобы начать!");
        } else {
            $bot->sendMessage($id, "Ответ принят! Вопрос: \"$question_text\"");
        }
    }
}