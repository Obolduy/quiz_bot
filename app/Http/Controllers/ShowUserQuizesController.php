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

            $bot->sendMessage($id, "\xE2\x9C\x85 Вы выбрали Вашу викторину *{$quiz->name}*.\n\xE2\xAD\x90 $stars\n\xF0\x9F\x93\x9D Чтобы *отредактировать викторину*, напишите /quiz\_change\n\xE2\x9D\x8E Чтобы *удалить викторину*, напишите /quiz\_delete\n\xE2\x9D\x93 Чтобы *пройти викторину самостоятельно*, напишите /quiz\_start", 'markdown');
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

        $stars_pic = '';
        for ($i = 1; $i <= $stars_avg; $i++) {
            $stars_pic .= " \xE2\xAD\x90";
        }

        switch ($stars_avg) {
            case '1':
                $stars_avg .= ' балл';
                break;
            case '5':
                $stars_avg .= ' баллов';
                break;
            default:
                $stars_avg .= ' балла';
        }

        $stars_text = ($votes_count || $stars_avg) ? 
            "Средняя оценка: $stars_pic _($stars_avg)_ на основе $votes_count $mark_text." :
            "Пока никто не оценил Вашу викторину.";

        return $stars_text;
    }
}