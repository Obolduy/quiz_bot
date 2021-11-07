<?php

namespace App\Http\Controllers;

use App\Models\{Quizes, Questions, Answers, PassedQuizes, CurrentUserQuiz, CorrectAnswers, QuizStars};
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
            Redis::hset($id, 'status_id', '3');
            Redis::hset($id, "quiz_id", $quiz->id);

            $bot->sendMessage($id, "Напишите 'Начать', чтобы начать викторину. \n Чтобы выйти, напишите /drop_quiz");
        } else if ($message_text == 'Далее') {
            (int)$page = Redis::hget($message->getChat()->getId(), 'page') ?? 1;
            Redis::hset($message->getChat()->getId(), 'page', ++$page);

            (new ShowQuizListController)->showQuizes($message, $bot);
        } else if ($message_text == 'Назад') {
            (int)$page = Redis::hget($message->getChat()->getId(), 'page') ?? 1;
            Redis::hset($message->getChat()->getId(), 'page', --$page);

            (new ShowQuizListController)->showQuizes($message, $bot);  
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
            if (Answers::where('answer', $message_text)->first()) {
                $this->saveAnswer($id, $message_text);
            } else {
                $bot->sendMessage($id, 'Введите, пожалуйста, корректный ответ'); die();
            }
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

            $keyboard = new ReplyKeyboardMarkup(
                [
                    ["1", "2", "3", "4", "5"]
                ], true, true
            );

            $bot->sendMessage($id, "Колличество набранных Вами баллов: $score. \n
                Пожалуйста, оцените викторину;)", null, false, null, $keyboard);
        } else {
            $bot->sendMessage($id, $question_text);
            $bot->sendMessage($id, 'Выберите правильный ответ', null, false, null, $keyboard);
        }
    }

    public function quizVote($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $quizStars = QuizStars::where('quiz_id', Redis::hget($id, 'quiz_id'))->first();

        if (in_array((int)$message_text, range(1,5))) { // является ли текст числом от 1 до 5
            $quizStars->votes_count++;
            $quizStars->stars_count += $message_text;
            $quizStars->stars_avg = $quizStars->stars_count / $quizStars->votes_count; // среднее арифметическое
            $quizStars->save();

            $bot->sendMessage($id, 'Спасибо, Ваш голос учтен!');
        }

        $bot->sendMessage($id, 'Чтобы посмотреть Ваши результаты по всем викторинам, напишите /results, для просмотра викторин - /quiz_list, а для создания - /quiz_create');

        Redis::hmset($id, "quiz_id", 0);
        Redis::hmset($id, "status_id", 4);
    }
}