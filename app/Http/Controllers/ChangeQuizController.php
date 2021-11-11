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
                
                $this->changeQuestion($update, $bot);

                break;
            case "ответ":
                Redis::hmset($id, 'status_id', '17');
                                
                $this->changeAnswerStart($update, $bot);

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

            $bot->sendMessage($id, $answer_list);
        } else {
            $matches = [];
            preg_match('#\[(.+)\]#u', $message_text, $matches);

            foreach ($answers as $answer) {
                if ($matches && $matches[1] !== $answer->question) {
                    continue;
                }

                if ($matches && $matches[1] === $answer->question) {
                    $message_text = trim(str_replace($matches[0], ' ', $message_text));
                }

                if ($answer->answer === $message_text) {
                    Redis::hset($id, 'status_id', '18');
                    Redis::hset($id, 'answer_id', $answer->id);
                    Redis::hset($id, 'question_id', $answer->question_id);

                    $bot->sendMessage($id, "Вы собираетесь изменить ответ \"$message_text\" к вопросу \"{$answer->question}\", введите новый ответ. Обратите внимание, что изменение ответа не влияет на то, является ли он правильным. \"Правильность\" ответа вы сможете указать в другом разделе."); die();
                }
            }

            $bot->sendMessage($id, "Ответ некорректный");
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