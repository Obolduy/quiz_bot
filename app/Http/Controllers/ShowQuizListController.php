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

        $pageFrom = ($page * 5) - 5; // вывод по 5 квизов
        $pageTo = 5;
        $quizes = Quizes::offset($pageFrom)
                ->limit($pageTo)
                ->get();

        if (!$quizes) {
            $quizes = Quizes::offset($page)
                    ->limit($pageTo)
                    ->get();
        }

        $quiz_list = [];

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
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

        $bot->sendMessage($message->getChat()->getId(),
        "Выберите викторину", null, false, null, $keyboard);
    }
}