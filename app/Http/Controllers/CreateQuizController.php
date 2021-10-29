<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreateQuizController extends Controller
{
    public function createQuiz($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();

        $bot->sendMessage($id, 'Все работает! ' . $message->getText());
    }
}
