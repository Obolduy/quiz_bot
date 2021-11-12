<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class ShowQuizListController extends Controller
{
    public function showQuizes($message, $bot)
    {
        if (Redis::hget($message->getChat()->getId(), 'status_id') != '9') {
            Redis::hmset($message->getChat()->getId(), 'status_id', '2');
        }

        $page = Redis::hget($message->getChat()->getId(), 'page') ?? 1;

        if ($page === 1) {
            $bot->sendMessage($message->getChat()->getId(),
                "Чтобы *отсортировать викторины* по _дате добавления_, введите /sort\_date, чтобы *сортировать* _как обычно_ - /quiz\_list", 'markdown');
        }

        $quizes = $this->paginateQuiz($message->getChat()->getId(), $page);

        $quiz_list = [];
        $quiz_message = '';

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;

            $grade = $quiz->stars_avg ?? 'Пока никто не поставил оценку:(';

            $quiz_message .= "\xF0\x9F\x93\x8C *Название викторины:* _{$quiz->name}_ \n \xE2\xAD\x90 *Средняя оценка:* _ $grade _ \n\n";
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
            ], true
        );
        
        $bot->sendMessage($message->getChat()->getId(), trim($quiz_message), 'markdown');

        $bot->sendMessage($message->getChat()->getId(),
            "\xE2\x9C\x85 *Выберите викторину*", 'markdown', false, null, $keyboard);
    }

    private function paginateQuiz($id, $page)
    {
        $pageFrom = ($page * 5) - 5; // вывод по 5 квизов
        $pageTo = 5;

        if (Redis::hget($id, 'status_id') == '9') { // сортировка по дате добавления (id)
            $quizes = Quizes::offset($pageFrom)
                        ->orderBy('id', 'desc')
                        ->limit($pageTo)
                        ->get();

            if (!$quizes) {
                $quizes = Quizes::offset($page)
                        ->orderBy('id', 'desc')
                        ->limit($pageTo)
                        ->get();
            }
        } else { // сортировка по средней оценке
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
        }

        return $quizes; 
    }
}