<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use Illuminate\Support\Facades\Redis;

class ChangeAnswerController extends Controller
{
    public function changeAnswerStart($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $answers = Answers::select('answers.id', 'answers.answer', 'questions.id AS question_id', 'questions.question')
                            ->leftJoin('questions', 'questions.id', '=', 'answers.question_id')
                            ->leftJoin('quizes', 'quizes.id', '=', 'questions.quiz_id')
                            ->where('quizes.id', Redis::hget($id, 'quiz_id'))
                            ->get();

        if (mb_strtolower($message_text, 'UTF-8') == 'ответ') {
            $this->sendAnswersList($bot, $id, $answers);
        } else {
            $this->checkAnswerName($bot, $id, $message_text, $answers);
        }
    }

    public function changeAnswer($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $answer = Answers::find(Redis::hget($id, 'answer_id'));

        $answer->answer = $message_text;
        $answer->save();

        Redis::hmset($id, 'status_id', '1');
        Redis::hdel($id, 'quiz_id');
        Redis::hdel($id, 'question_id');
        Redis::hdel($id, 'answer_id');

        $bot->sendMessage($id, "Ответ успешно изменен.");
    }

    private function sendAnswersList($bot, $user_id, $answers)
    {
        $answer_list = '';
        $questions = [];
        
        foreach ($answers as $answer) {
            if (!in_array($answer->question, $questions)) {
                $questions[] = $answer->question;

                $answer_list .= "Вопрос \"{$answer->question}\" \n";
            }

            $answer_list .= $answer->answer . "\n";
        }
        
        $answer_list .= 'Если у Вас есть одинаковые ответы на разные вопросы, укажите в квадратных скобках вопрос, ответ к которому Вы хотите изменить. Если Вы хотите указать, какой вопрос считается правильным, напишите /change_correct_answer';

        $bot->sendMessage($user_id, $answer_list);
    }

    private function checkAnswerName($bot, $user_id, $message_text, $answers)
    {
        $matches = [];
        preg_match('#\[(.+)\]#u', $message_text, $matches);

        foreach ($answers as $answer) {
            $message_text = $this->checkQuestionName($message_text, $answer);

            if ($answer->answer === $message_text) {
                Redis::hset($user_id, 'status_id', '18');
                Redis::hset($user_id, 'answer_id', $answer->id);
                Redis::hset($user_id, 'question_id', $answer->question_id);

                $bot->sendMessage($user_id, "Вы собираетесь изменить ответ \"$message_text\" к вопросу \"{$answer->question}\", введите новый ответ. Обратите внимание, что изменение ответа не влияет на то, является ли он правильным. \"Правильность\" ответа вы сможете указать в другом разделе."); die();
            }
        }

        $bot->sendMessage($user_id, "Ответ некорректный");
    }

    private function checkQuestionName($message_text, $answer): string
    {
        $matches = [];
        preg_match('#\[(.+)\]#u', $message_text, $matches);

        if ($matches && $matches[1] === $answer->question) {
            $message_text = trim(str_replace($matches[0], ' ', $message_text));
        }

        return $message_text;
    }
}