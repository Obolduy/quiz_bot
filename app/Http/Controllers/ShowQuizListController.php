<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\{Message, ReplyKeyboardMarkup};

class ShowQuizListController extends Controller
{
    /**
     * Shows list of quizes sorted by 5 quizes per page
     * @param Message
     * @param Client
     * @return void
     */

    public function showQuizes(Message $message, Client $bot): void
    {
        if (Redis::hget($message->getChat()->getId(), 'status_id') != '9') {
            Redis::hmset($message->getChat()->getId(), 'status_id', '2');
        }

        $page = Redis::hget($message->getChat()->getId(), 'page') ?? 1;

        if ($page === 1) {
            $bot->sendMessage($message->getChat()->getId(),
                "Чтобы *отсортировать викторины* по _дате добавления_, введите /sort\_date, чтобы *сортировать* _как обычно_ - /quiz\_list", 'markdown');
        }

        $quizes = (new PaginationController)->paginateQuiz($message->getChat()->getId(), $page, null);

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
}