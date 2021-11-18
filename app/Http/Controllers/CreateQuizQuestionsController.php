<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class CreateQuizQuestionsController extends Controller
{
    /**
     * Sets question and question picture if exists by user's message
     * @param Update
     * @param Client
     * @return void
     */

    public function createQuizQuestions(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $question = trim(strip_tags($message->getText()));

        if ($question == '') { // если текст - это не сообщение, а подпись к фото
            $question = trim(strip_tags($message->getCaption()));
        }

        $message_array = str_split($question);
        $message_photo = $message->getPhoto();

        if (!in_array('?', $message_array)) {
            array_push($message_array, '?');
        }

        $question = implode('', $message_array);

        $question_number = count(Redis::hgetall($id."_create_quiz"));
        $question_number = ($question_number == 1) ? 1 : $question_number; // нумеровать вопросы с единицы

        Redis::hset($id."_create_quiz", "question_$question_number", $question);

        if ($message_photo) {
            $picture_name = (new AddPicturesController)->addQuestionPicture($message_photo);
            Redis::hset($id."_create_quiz_pictures", "picture_$question_number", $picture_name);
        }

        $bot->sendMessage($id, 'Вопрос добавлен! Введите следующий или, если хотите закончить ввод, напишите "/add_questions_stop"');
    }
}