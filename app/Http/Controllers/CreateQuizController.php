<?php

namespace App\Http\Controllers;

use App\Models\{Answers, CorrectAnswers, QuestionPictures, Questions, Quizes, QuizStars};
use Illuminate\Support\Facades\{Redis, DB};
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class CreateQuizController extends Controller
{
    /**
     * Starts creating quiz
     * @param Message
     * @param Client
     * @return void
     */

    public function createQuizStart(Message $message, Client $bot): void
    {
        Redis::hmset($message->getChat()->getId(), 'status_id', '5');

        $bot->sendMessage($message->getChat()->getId(),
            'Введите название Вашей викторины, не превышающее 64 символов! 
                Пожалуйста, будьте корректны в выборе наименования:)');
    }

    /**
     * Adds new quiz data from Redis into DB 
     * @param int user's id
     * @return void
     */

    public function createQuizDone(int $id): void
    {
        DB::transaction(function () use ($id) {
            $quiz = Quizes::create([
                'name' => Redis::hget($id."_create_quiz", 'quiz_name'),
                'creator_id' => $id
            ]);

            QuizStars::create([
                'quiz_id' => $quiz->id, 
            ]);

            for ($i = 1; $i < count(Redis::hgetall($id."_create_quiz")); $i++) {
                $question = Questions::create([
                    'quiz_id' => $quiz->id,
                    'question' => Redis::hget($id."_create_quiz", "question_$i")
                ]);

                if (Redis::hget($id."_create_quiz_pictures", "picture_$i")) {
                    QuestionPictures::create([
                        'question_id' => $question->id,
                        'picture' => Redis::hget($id."_create_quiz_pictures", "picture_$i")
                    ]);
                }

                $questions[$i] = $question;
                $answers_list[$question->id] = Redis::hgetall($id."_create_answers_question_$i");
            }
            
            foreach ($answers_list as $question_id => $answers) {
                foreach ($answers as $answer) {
                    $all_answers[] = Answers::create([
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                }
            }

            foreach ($questions as $question) {
                foreach ($all_answers as $answer) {
                    if ($answer->question_id == $question->id) {
                        foreach (Redis::hgetall($id."_create_correct_answers_question") as $key => $value) {
                            if ($question->question == $key && $answer->answer == $value) {
                                CorrectAnswers::create([
                                    'question_id' => $answer->question_id,
                                    'answer_id' => $answer->id
                                ]);
                            }
                        }
                    }
                }
            }
        });

        for ($i = 1; $i < count(Redis::hgetall($id."_create_quiz")); $i++) {
            Redis::del($id."_create_answers_question_$i");
        }

        Redis::del($id."_create_quiz");
        Redis::del($id."_create_correct_answers_question");
        Redis::del($id."_create_quiz_pictures");
    }
}