<?php

namespace App\Http\Controllers;

use App\Models\{Quizes, Questions, Answers, PassedQuizes, CurrentUserQuiz, CorrectAnswers};
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ShowQuizController extends Controller
{
    public function selectQuizByName($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $quiz = Quizes::where('name', $message_text)->first();

        if ($quiz) {
            Redis::hmset($id, 'status_id', '3');
            Redis::hmset($id, "quiz_id", $quiz->id);

            $bot->sendMessage($id, "Напишите 'Начать', чтобы начать викторину");
        } else {
            $bot->sendMessage($id, 'Название викторины неверно!');
        }
    }

    public function saveAnswer($id, $message_text)
    {
        $current_user_quiz = CurrentUserQuiz::where('user_id', $id)->orderByDesc('id')->first();

        $answer = Answers::where('question_id', $current_user_quiz->passed_question_id)->
            where('answer', $message_text)->first();

        $current_user_quiz->passed_answer_id = $answer->id;
        $current_user_quiz->save();
    }

    public function quiz($update, $bot)
    {   
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));
        
        $quiz_id = Redis::hget($id, 'quiz_id');

        if (mb_strtolower($message_text, 'UTF-8') !== 'начать' && CurrentUserQuiz::where('user_id', $id)->first()) {
            $this->saveAnswer($id, $message_text);
        } 
        
        $questions = Questions::where('quiz_id', $quiz_id)->get();

        $question_text = '';

        foreach ($questions as $question) {
            $passed_questions = CurrentUserQuiz::where('user_id', $id)
                                ->where('passed_question_id', $question->id)
                                ->first();

            if (!$passed_questions) {
                $question_text = $question->question;

                $answers = Answers::where('question_id', $question->id)->get();

                CurrentUserQuiz::create([
                    'quiz_id' => $quiz_id,
                    'user_id' => $id,
                    'passed_question_id' => $question->id
                ]);

                foreach ($answers as $answer) {
                    $answer_list[] = $answer->answer;
                }
        
                $keyboard = new ReplyKeyboardMarkup(
                    [
                        $answer_list
                    ], true
                );

                break;
            }
        }

        if ($question_text == '') {
            $score = 0;

            $current_user_quiz = CurrentUserQuiz::where('user_id', $id)
                                ->where('quiz_id', $quiz_id)->get();
            
            foreach ($current_user_quiz as $elem) {
                $correct_answers = CorrectAnswers::where('question_id', $elem->passed_question_id)
                                    ->first();


                if ($correct_answers->answer_id == $elem->passed_answer_id) {
                    $score++;
                }
            }

            PassedQuizes::create([
                'passed_quiz_id' => $quiz_id, 
                'user_id' => $id,
                'total_score' => $score
            ]);
        }

        if (isset($score)) {
            foreach ($current_user_quiz as $elem) {
                $elem->delete();
            }

            Redis::hmset($id, "status_id", 4);
            Redis::hmset($id, "quiz_id", 0);

            $bot->sendMessage($id, "Колличество набранных Вами баллов: $score");
        } else {
            $bot->sendMessage($id, $question_text);
            $bot->sendMessage($id, 'Выберите правильный ответ', null, false, null, $keyboard);
        }
    }  
}