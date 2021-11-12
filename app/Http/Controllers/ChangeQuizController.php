<?php

namespace App\Http\Controllers;

use App\Models\{Answers, CorrectAnswers, Questions, Quizes};
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

        switch ($message_text) {
            case "название":
                Redis::hmset($id, 'status_id', '14');

                $this->changeQuizName($update, $bot);

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

    public function changeCorrectAnswerStart($message, $bot)
    {
        $id = $message->getChat()->getId();

        Redis::hmset($id, 'status_id', '19');

        $questions = Questions::where('quiz_id', Redis::hget($id, 'quiz_id'))->get();

        $questions_list = [];
        foreach ($questions as $question) {
            $questions_list[] = $question->question;
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $questions_list
            ], 
            true
        );

        $bot->sendMessage($id, 'Выберите вопрос', null, false, null, $keyboard);
    }

    public function changeCorrectAnswerGetAnswers($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $question = Questions::where('quiz_id', Redis::hget($id, 'quiz_id'))
                                ->where('question', $message_text)->first();

        Redis::hset($id, 'question_id', $question->id);
        
        $answers = Answers::where('question_id', $question->id)->get();
        $correct_answer = CorrectAnswers::where('question_id', $question->id)->first();

        $answers_list = [];
        foreach ($answers as $answer) {
            if ($answer->id == $correct_answer->answer_id) {
                $answers_list[] = $answer->answer . ' (Правильный)';
            } else {
                $answers_list[] = $answer->answer;
            }
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $answers_list
            ], 
            true
        );

        Redis::hmset($id, 'status_id', '20');
        $bot->sendMessage($id, 'Выберите ответ, чтобы сделать его правильным.', null, false, null, $keyboard);
    }

    public function changeCorrectAnswerSignAnswers($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        preg_match('#\(Правильный\)#u', $message_text, $matches);

        if ($matches) {
            $message_text = trim(str_replace($matches, '', $message_text));
        }

        $answer = Answers::where('question_id', Redis::hget($id, 'question_id'))->where('answer', $message_text)->first();
        $correct_answer = CorrectAnswers::where('question_id', Redis::hget($id, 'question_id'))->first();

        if ($answer) {
            $correct_answer->answer_id = $answer->id;
            $correct_answer->save();
    
            Redis::del($id);
            Redis::hmset($id, 'status_id', '1');
    
            $bot->sendMessage($id, 'Правильный ответ успешно изменен');
        } else {
            $bot->sendMessage($id, 'Ответ некорректен');
        }
    }
}