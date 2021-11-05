<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ShowQuizListController extends Controller
{
    public function showQuizes($message, $bot)
    {
        Redis::hmset($message->getChat()->getId(), 'status_id', '2');

        $page = Redis::hget($message->getChat()->getId(), 'page') ?? 1;

        $quizes = $this->paginateQuiz($page);

        $quiz_list = [];
        $quiz_message = '';

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
            $quiz_message .= "Название викторины: {$quiz->name} \n Средняя оценка: {$quiz->stars_avg} \n";
        }

        if ((int)$page !== 1) {
            $quiz_list[] = 'Назад';
        }

        if (count($quizes) >= 5) {
            $quiz_list[] = 'Далее';
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                $quiz_list
            ], true);

        
        $bot->sendMessage($message->getChat()->getId(), $quiz_message);

        $bot->sendMessage($message->getChat()->getId(),
            "Выберите викторину", null, false, null, $keyboard);
    }

    private function paginateQuiz($page)
    {
        $pageFrom = ($page * 5) - 5; // вывод по 5 квизов
        $pageTo = 5;
        $quizes = Quizes::select('quizes.*', 'quiz_stars.stars_avg')
                ->offset($pageFrom)
                ->leftJoin('quiz_stars', 'quizes.id', '=', 'quiz_stars.quiz_id')
                ->orderBy('quiz_stars.stars_avg', 'desc')
                ->limit($pageTo)
                ->get();

        if (!$quizes) {
            $quizes = Quizes::select('quizes.*', 'quiz_stars.stars_avg')
                    ->offset($page)
                    ->leftJoin('quiz_stars', 'quizes.id', '=', 'quiz_stars.quiz_id')
                    ->orderBy('quiz_stars.stars_avg', 'desc')
                    ->limit($pageTo)
                    ->get();
        }

        return $quizes; // сортировка по средней оценке
    }
}