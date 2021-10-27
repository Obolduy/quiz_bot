<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use TelegramBot\Api\{BotApi, Client};

Route::any('/biba', [TestController::class, 'test']);

Route::any('/', function () {
    try {
        $telegram = new BotApi('2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o');
        $bot = new Client('2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o');
    
        // $bot->command('start', function ($message) use ($bot, $telegram) {
        //     $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([
        //         ["one1", "two2", "three3"]
        //     ], true);
        //     $bot->sendMessage($message->getChat()->getId(), 'pong!', null, false, null, $keyboard);
        // });

        $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {
            $message = $update->getMessage();
            $id = $message->getChat()->getId();
            
            DB::raw("CREATE TABLE IF NOT EXISTS current_quiz_$id (
                id INT PRIMARY KEY auto_increment,
                passed_answer_id INT NOT NULL
            )");

            $questions = DB::table('questions')
                        ->leftJoin('answers', 'questions.id', '=', 'answers.question_id')
                        ->select('questions.question', 'questions.id', 'answers.answer', 'answers.id AS answer_id')
                        ->get();

            foreach ($questions as $question) {
                if (!DB::table("current_quiz_$id")->select('*')->where('passed_answer_id', '=', $question->answer_id)->first()) {
                    $question_text = $question->question;
                    
                    $answer_buttons[] = $question->answer;
                }
            }

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([
                $answer_buttons
            ], true);
            
            $bot->sendMessage($message->getChat()->getId(), $question_text, null, false, null, $keyboard);

            // $bot->sendMessage($id, 'Your message: ' . $message->getText(), null, false, null, $keyboard);
        }, function () {
            return true;
        });

        $bot->run();
    } catch (\TelegramBot\Api\Exception $e) {
        $e->getMessage();
    }
    

    // $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {
    //     $message = $update->getMessage();
    //     $id = $message->getChat()->getId();
    //     $bot->sendMessage($id, 'Твой варик: ' . $message->getText());
    // }, function () {
    //     return true;
    // });
    
    
    // 'https://api.telegram.org/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/setWebhook?url=https://a7dc-95-55-158-244.ngrok.io'
});
