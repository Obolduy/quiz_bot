<?php

namespace App\Http\Controllers;

use App\Models\{Quizes, Questions, Answers, CorrectAnswers, CurrentUserQuiz, QuestionPictures};
use Illuminate\Support\Facades\{Redis, Storage};
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\{Message, Update};

class DeleteQuizController extends Controller
{
    /**
     * Asks user about quiz deletion
     * @param Message
     * @param Client
     * @return void
     */

    public function deleteQuizConfirmation(Message $message, Client $bot): void
    {
        $id = $message->getChat()->getId();

        Redis::hmset($id, 'status_id', '12');

        $bot->sendMessage($id, 'Вы уверены, что хотите удалить викторину? Напишите "Да" или "Нет" соответственно Вашему решению.');
    }

    /**
     * Deletes quiz (name, questions, answers, correct answers and pictures)
     * @param Update
     * @param Client
     * @return void
     */

    public function deleteQuiz(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        if (mb_strtolower($message_text, 'UTF-8') === 'да') {
            $quiz = Quizes::find(Redis::hget($id, 'quiz_id'));
            $questions = Questions::where('quiz_id', $quiz->id)->get();

            foreach ($questions as $question) {
                $answers = Answers::where('question_id', $question->id)->get();
                foreach ($answers as $answer) {
                    $answer->delete();
                }

                $correct_answers = CorrectAnswers::where('question_id', $question->id)->get();
                foreach ($correct_answers as $correct_answer) {
                    $correct_answer->delete();
                }

                $picture = QuestionPictures::where('question_id', $question->id)->first();
                if ($picture) {
                    Storage::delete("questions/{$picture->picture}");

                    $picture->delete();
                }

                $question->delete();
            }

            $current_user_quiz = CurrentUserQuiz::where('quiz_id', $quiz->id)->get();
            foreach ($current_user_quiz as $current_quiz) {
                $current_quiz->delete();
            }
            
            $quiz->delete();

            Redis::hset($id, 'quiz_id', 0);
            Redis::hset($id, 'status_id', 1);

            $bot->sendMessage($id, 'Вы успешно удалили викторину!');
            (new MainController)->mainPage($bot, $message->getChat()->getId());
        } else {
            $bot->sendMessage($id, 'Удаление отменено');
        }
    }
}