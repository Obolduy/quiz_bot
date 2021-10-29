<?php

use App\Http\Controllers\CreateQuizController;
use App\Http\Controllers\TestController;
use App\Models\Quizes;
use Illuminate\Support\Facades\{Route, Redis};
use TelegramBot\Api\{BotApi, Client};

Route::any('/biba', [TestController::class, 'test']);

Route::any('/', function () {
    // 1 - Пользователь ввел '/start'
    // 2 - Пользователь открыл выбор квизов
    // 3 - Пользователь проходит квиз
    // 4 - Пользователь прошел квиз, показ результатов
    // 5 - Пользователь создает квиз

    // $telegram = new BotApi('2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o');
    $bot = new Client('2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o');

    $bot->command('start', function ($message) use ($bot) {
        Redis::hmset($message->getChat()->getId(), 'status_id', '1');

        $bot->sendMessage($message->getChat()->getId(),
            'Чтобы создать викторину, напиши /quiz_create, чтобы выбрать готовую, введи /quiz_list');
    });

    $bot->command('quiz_list', function ($message) use ($bot) {
        Redis::hmset($message->getChat()->getId(), 'status_id', '2');

        $quizes = Quizes::all();

        $quiz_list = [];

        foreach ($quizes as $quiz) {
            $quiz_list[] = $quiz->name;
        }

        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
            [
                $quiz_list
            ], true);

        $bot->sendMessage($message->getChat()->getId(),
            'Выберите викторину', null, false, null, $keyboard);
    });

    
    // $bot->sendMessage($id, 'Your message: ' . $message->getText(), null, false, null, $keyboard);
    
    $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();

        switch (Redis::hmget($id, 'status_id')[0]) {
            case 1:

                break;
            case 2:

                break;
            case 3:

                break;
            case 4:

                break;
            case 5:
                (new CreateQuizController)->createQuiz($update, $bot);
                break;
        }
    }, function () {
        return true;
    });
    
    $bot->run();
    // Applications/ngrok http test_bot.local
    // 'https://api.telegram.org/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/setWebhook?url=https://3a5d-178-66-255-65.ngrok.io'
});
