<?php

namespace App\Http\Controllers;

use App\Models\{Questions, QuestionPictures};
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\{ReplyKeyboardMarkup, Update};
use TelegramBot\Api\Types\InputMedia\{ArrayOfInputMedia, InputMediaPhoto};

class ChangeQuestionController extends Controller
{
    /**
     * Sends question list and signs new question (and picture) into DB
     * @param Update
     * @param Client
     * @return void
     */

    public function changeQuestion(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));
        $message_photo = $message->getPhoto();

        if ($message_text == '') { // если текст - это не сообщение, а подпись к фото
            $message_text = trim(strip_tags($message->getCaption()));
        }

        $questions = Questions::where('quiz_id', Redis::hget($id, 'quiz_id'))->get();

        $questions_pictures = [];
        foreach ($questions as $question) {
            $questions_list[] = [$question->question];

            $picture = QuestionPictures::where('question_id', $question->id)->value('picture');

            if ($picture) {
                $questions_pictures[$question->question] = $picture;
            }
        }

        // если пользователь написал "вопрос", вывести список вопросов
        if (mb_strtolower($message_text, 'UTF-8') == 'вопрос') {
            $this->sendQuestionsList($bot, $id, $questions_list);

            if ($questions_pictures) {
                $this->sendQuestionsPictures($bot, $id, $questions_pictures);
            }
        }

        // status_id=16 означает, что бот ожидает, что сообщение пользователя - новый вопрос
        if (Redis::hget($id, 'status_id') == 16) {
            $question = Questions::find(Redis::hget($id, 'question_id'));

            $this->setNewQuestionTitle($message_text, $question);

            if ($message_photo) {
                $picture_name = (new AddPicturesController)->addQuestionPicture($message_photo);

                $picture = QuestionPictures::firstOrCreate(
                    ['question_id' => $question->id],
                    ['picture' => $picture_name]
                );
                $picture->picture = $picture_name;
                $picture->save();
            }

            Redis::hmset($id, 'status_id', '1');
            Redis::hdel($id, 'quiz_id');
            Redis::hdel($id, 'question_id');

            $bot->sendMessage($id, "Вопрос успешно изменен, не забудьте обновить ответы к нему!");
        } else { // бот ожидает, что сообщение - это текст вопроса для изменению
            $this->rememberSelectedQuestionId($bot, $message_text, $id, $questions, $questions_list);
        }
    }

    /**
     * Sends keyboard with questions list
     * @param Client
     * @param int user's id
     * @param array list of questions by quiz id
     * @return void
     */

    private function sendQuestionsList(Client $bot, int $user_id, array $questions_list): void
    {
        $keyboard = new ReplyKeyboardMarkup($questions_list, true);

        $bot->sendMessage($user_id, 'Выберите, какой вопрос Вы хотите отредактировать', null, false, null, $keyboard);
    }

    /**
     * Sends question's pictures
     * @param Client
     * @param int user's id
     * @param array question text => picture name
     * @return void
     */

    private function sendQuestionsPictures(Client $bot, int $user_id, array $questions_pictures): void
    {
        $question_text = "К следующим вопросам имеются изображения: \n";

        $media = new ArrayOfInputMedia();
        foreach ($questions_pictures as $question => $picture) {
            $media->addItem(new InputMediaPhoto(asset("questions/$picture"), $question));
            $question_text .= "*$question* \n";
        }

        $bot->sendMessage($user_id, $question_text, 'markdown');
        $bot->sendMediaGroup($user_id, $media);
    }

    /**
     * Sets question id into Redis
     * @param Client
     * @param string user's message
     * @param int user's id
     * @param Questions questions by quiz id
     * @param array array with questions names
     * @return void
     */

    private function rememberSelectedQuestionId(Client $bot, string $message_text, int $user_id, Questions $questions, array $questions_list): void
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

    /**
     * Adds '?' if required into new question text and saves it into DB 
     * @param string user's message
     * @param Questions selected row from questions table
     * @return void
     */

    private function setNewQuestionTitle(string $message_text, Questions $question): void
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