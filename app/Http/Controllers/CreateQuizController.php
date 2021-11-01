<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\Questions;
use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;

class CreateQuizController extends Controller
{
    public function createQuizStart($message, $bot)
    {
        Redis::hmset($message->getChat()->getId(), 'status_id', '5');

        $bot->sendMessage($message->getChat()->getId(),
            'Введите название Вашей викторины, не превышающее 64 символов! 
                Пожалуйста, будьте корректны в выборе наименования:)');
    }

    public function createQuizName($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        if (Quizes::where('name', $message_text)->first()) {
            $bot->sendMessage($id, 'Данное название уже используется, пожалуйста, придумайте другое');
        } else {
            Redis::hmset($id, 'status_id', '6');
            Redis::hset($id."_create_quiz", 'quiz_name', $message_text);

            $bot->sendMessage($id, 'Отлично, теперь пора придумывать вопросы! Введите и отправьте свой вопрос');
        }
    }

    public function createQuizQuestion($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_array = str_split(trim(strip_tags($message->getText())));

        if (!in_array('?', $message_array)) {
            array_push($message_array, '?');
        }

        $question = implode('', $message_array);

        $question_number = count(Redis::hgetall($id."_create_quiz"));
        $question_number = ($question_number == 1) ? 1 : $question_number; // нумеровать вопросы с единицы

        Redis::hset($id."_create_quiz", "question_$question_number", $question);

        $bot->sendMessage($id, 'Вопрос добавлен! Введите следующий или, если хотите закончить ввод, напишите "/add_questions_stop"');
    }

    public function createQuizAnswerStart($message, $bot)
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

        $bot->sendMessage($message->getChat()->getId(),
            "Мы закончили с вопросами! Теперь пора придумывать ответы на них. 
            Введите ответ и отправьте его, после 4-го варианта Вы будете автоматически переведены 
            на следующий вопрос. Правильные ответы Вы сможете пометить чуть позже.
            Для начала, введите первый ответ на Ваш вопрос \"{$questions['question_1']}\"");
    }

    public function createQuizAnswers($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $questions_count = Redis::hgetall($id."_create_quiz");

        $is_done = false;
        for ($i = 1; $i < count($questions_count); $i++) {
            $question = Redis::hgetall($id."_create_answers_question_$i");

            $answer_count = count($question);

            if ($answer_count == 3 && $i == (count($questions_count) - 1)) {
                $is_done = true;
            }

            if ($question["answer_1"] == 'empty') {
                Redis::hset($id."_create_answers_question_$i", "answer_1", $message_text);

                break;
            }

            if ($answer_count < 4) {
                $answer_count++;
                Redis::hset($id."_create_answers_question_$i", "answer_$answer_count", $message_text);

                break;
            }
        }

        if ($is_done) {
            Redis::hmset($id, 'status_id', '8');
            $bot->sendMessage($message->getChat()->getId(),
                "Мы закончили с ответами! Самое время выбрать правильные ответы!"); // аналогично ниже
        }

        $bot->sendMessage($message->getChat()->getId(),
            "Ответ принят!"); // над текстом (выводом вопроса) подумай
    }

    // public function createQuizCorrectAnswers($update, $bot)
    // {
    //     $message = $update->getMessage();
    //     $id = $message->getChat()->getId();
    //     $message_text = trim(strip_tags($message->getText()));

    //     $answers = Redis::hgetall($id."_create_correct_answers_question");

    //     if (Redis::hgetall($id."_create_correct_answers_question")) {
    //         Redis::hset($id."_create_correct_answers_question", "question_1", $message_text);
    //     } else {
    //         $questions = Redis::hgetall($id."_create_quiz");

    //         $count = 1;
    //         foreach ($questions as $key => $value) {
    //             if ($key != 'quiz_name') {
    //                 $answers[$count] = Redis::hgetall($id."_create_answers_question_$count");
    //                 $count++;
    //             }
    //         }
    //     }
    // }
}