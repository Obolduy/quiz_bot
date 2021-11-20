<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;

class MainController extends Controller
{
    /**
     * Deletes user's data from Redis and shows welcome message
     * @param Client
     * @param int user's id
     * @return void
     */

    public function mainPage(Client $bot, int $user_id): void
    {
        Redis::del($user_id);
        Redis::del($user_id."_quizes_pagination");
        Redis::hmset($user_id, 'status_id', '1');

        $bot->sendMessage($user_id,
            "\xF0\x9F\x86\x95 Чтобы *создать викторину*, напишите /quiz\_create \n\xE2\x9C\x85 Чтобы *выбрать готовую викторину*, напишите /quiz\_list \n\xF0\x9F\x8E\x93Чтобы *посмотреть Ваши созданные викторины*, напишите /my\_quizes", 'markdown');
    }
}
