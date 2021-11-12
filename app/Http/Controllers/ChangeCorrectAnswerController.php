<?php

namespace App\Http\Controllers;

use App\Models\{Answers, CorrectAnswers, Questions};
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ChangeCorrectAnswerController extends Controller
{
    public function getQuestionsByQuizId($message, $bot)
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

    public function getAnswersWithCorrect($update, $bot)
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

        $this->showAnswers($answers_list, $id, $bot);
    }

    public function signCorrectAnswer($update, $bot)
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

    private function showAnswers($answers_list, $user_id, $bot)
    {
        $keyboard = new ReplyKeyboardMarkup(
            [
                $answers_list
            ], 
            true
        );

        Redis::hmset($user_id, 'status_id', '20');
        
        $bot->sendMessage($user_id, 'Выберите ответ, чтобы сделать его правильным.', null, false, null, $keyboard);
    }
}