<?php

use App\Http\Controllers\{
    CreateQuizController, ShowQuizController, TestController, ShowUserQuizesController,
    CreateQuizAnswersController, CreateQuizCorrectAnswersController, CreateQuizNameController,
    CreateQuizQuestionsController, ShowQuizListController, ShowUserResults, DeleteQuizController
};
use App\Models\CurrentUserQuiz;
use Illuminate\Support\Facades\{Route, Redis};
use TelegramBot\Api\{BotApi, Client};
use TelegramBot\Api\Types\Update;

Route::any('/biba', [TestController::class, 'test']);

Route::any('/', function () {
    // 1 - Пользователь ввел '/start'
    // 2 - Пользователь открыл выбор квизов
    // 3 - Пользователь проходит квиз
    // 4 - Пользователь прошел квиз, показ результатов, оценка
    // 5 - Пользователь создает квиз (Ввод названия)
    // 6 - Пользователь создает квиз (Ввод вопросов)
    // 7 - Пользователь создает квиз (Ввод ответов)
    // 8 - Пользователь создает квиз (Выбор правильных ответов)
    // 9 - Пользователь открыл выбор квизов и отсортировал их по дате
    // 10 - Пользователь открыл просмотр всех своих результатов
    // 11 - Пользователь открыл взаимодействие со своими квизами
    // 12 - Пользователь собирается удалить квиз

    // $telegram = new BotApi('2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o');
    $bot = new Client('2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o');

    $bot->command('start', function ($message) use ($bot) {
        Redis::hmset($message->getChat()->getId(), 'status_id', '1');

        $bot->sendMessage($message->getChat()->getId(),
            'Чтобы создать викторину, напиши /quiz_create, чтобы выбрать готовую, введи /quiz_list');
    });

    $bot->command('quiz_list', function ($message) use ($bot) {
        (new ShowQuizListController)->showQuizes($message, $bot);
    });

    $bot->command('sort_date', function ($message) use ($bot) {
        Redis::hmset($message->getChat()->getId(), 'status_id', '9');
        (new ShowQuizListController)->showQuizes($message, $bot);
    });

    $bot->command('quiz_create', function ($message) use ($bot) {
        (new CreateQuizController)->createQuizStart($message, $bot);
    });

    $bot->command('add_questions_stop', function ($message) use ($bot) {
        (new CreateQuizAnswersController)->createQuizAnswerStart($message, $bot);
    });

    $bot->command('my_quizes', function ($message) use ($bot) {
        (new ShowUserQuizesController)->showUserQuizes($message, $bot);
    });

    $bot->command('quiz_delete', function ($message) use ($bot) {
        (new DeleteQuizController)->deleteQuizConfirmation($message, $bot);
    });

    $bot->command('results', function ($message) use ($bot) {
        Redis::hmset($message->getChat()->getId(), 'status_id', '10');
        (new ShowUserResults)->showResults($message, $bot);
    });

    $bot->command('drop_quiz', function ($message) use ($bot) {
        Redis::del($message->getChat()->getId());
        $quiz = CurrentUserQuiz::where('user_id', $message->getChat()->getId())->get();

        foreach ($quiz as $elem) {
            $elem->delete();
        }

        $bot->sendMessage($message->getChat()->getId(), 'Тест успешно отменен!');
    });
    
    $bot->on(function (Update $update) use ($bot) {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();

        switch (Redis::hmget($id, 'status_id')[0]) {
            case 1:

                break;
            case 2:
                (new ShowQuizController)->selectQuizByName($update, $bot);
                break;
            case 3:
                (new ShowQuizController)->quiz($update, $bot);
                break;
            case 4:
                (new ShowQuizController)->quizVote($update, $bot);
                break;
            case 5:
                (new CreateQuizNameController)->createQuizName($update, $bot);
                break;
            case 6:
                (new CreateQuizQuestionsController)->createQuizQuestions($update, $bot);
                break;
            case 7:
                (new CreateQuizAnswersController)->createQuizAnswers($update, $bot);
                break;
            case 8:
                (new CreateQuizCorrectAnswersController)->createQuizCorrectAnswers($update, $bot);
                break;
            case 9:
                (new ShowQuizController)->selectQuizByName($update, $bot);
                break;
            case 10:
                (new ShowQuizController)->selectQuizByName($update, $bot);
                break;
            case 11:
                (new ShowUserQuizesController)->selectUserQuiz($update, $bot);
                break;
            case 12:
                (new DeleteQuizController)->deleteQuiz($update, $bot);
                break;
        }
    }, function () {
        return true;
    });
    
    $bot->run();
    // Applications/ngrok http test_bot.local
    // 'https://api.telegram.org/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/setWebhook?url=https://3a5d-178-66-255-65.ngrok.io'
});