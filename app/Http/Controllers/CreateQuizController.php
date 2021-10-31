<?php

namespace App\Http\Controllers;

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

        $question_number = (count(Redis::hgetall($id."_create_quiz")) - 1);
        $question_number = ($question_number !== 0) ? $question_number : 1;

        Redis::hmset($id."_create_quiz", "question_$question_number", $question);

        $bot->sendMessage($id, 'Вопрос добавлен! Введите следующий или, если хотите закончить ввод, напишите "/add_questions_stop"');
    }
}
