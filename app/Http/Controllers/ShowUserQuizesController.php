<?php

namespace App\Http\Controllers;

use App\Models\{Quizes, QuizStars};
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ShowUserQuizesController extends Controller
{
    public function showUserQuizes($message, $bot)
    {
        $id = $message->getChat()->getId();

        $quizes = Quizes::where('creator_id', $id)->get();

        Redis::hmset($id, 'status_id', '11');

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $quiz_list
            ], 
            true
        );

        $bot->sendMessage($id, 'Выберите викторину, чтобы начать с ней взаимодействие', null, false, null, $keyboard);
    }

    public function selectUserQuiz($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $quiz = Quizes::where('name', $message_text)->where('creator_id', $id)->first();

        if ($quiz) {
            Redis::hmset($id, "quiz_id", $quiz->id);

            $stars = $this->getQuizStars($quiz->id);

            $bot->sendMessage($id, "Вы выбрали Вашу викторину {$quiz->name}.
            $stars
            Чтобы отредактировать викторину, напишите /quiz_change, чтобы удалить - /quiz_delete, а чтобы пройти её самостоятельно, напишите /quiz_start");
        } else {
            $bot->sendMessage($id, 'Уточните название викторины');
        }
    }

    private function getQuizStars($quiz_id): string
    {
        $stars = QuizStars::where("quiz_id", $quiz_id)->first();
        $stars_avg = $stars->stars_avg;
        $votes_count = $stars->votes_count;

        $mark_text = '';
        switch (substr($votes_count, -1)) {
            case '1':
                $mark_text = 'оценки';
                break;
            default:
                $mark_text = 'оценок';
        }

        $stars_text = ($votes_count || $stars_avg) ? 
            "Средняя оценка составляет $stars_avg на основе $votes_count $mark_text." :
            "Пока никто не оценил Вашу викторину.";

        return $stars_text;
    }
}