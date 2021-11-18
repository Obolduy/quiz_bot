<?php

namespace App\Http\Controllers;

use App\Models\{Quizes, Questions};
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class ShowUserResults extends Controller
{
    /**
     * Sends to user his scores
     * @param Message
     * @param Client
     * @return void
     */

    public function showResults(Message $message, Client $bot): void
    {
        $results = Quizes::select('passed_quizes.*', 'quizes.name')
                    ->leftJoin('passed_quizes', 'quizes.id', '=', 'passed_quizes.passed_quiz_id')
                    ->where('passed_quizes.user_id', $message->getChat()->getId())
                    ->orderBy('passed_quizes.total_score', 'desc')
                    ->distinct()
                    ->get();

        $results_message = '';
        $quiz_id = [];

        foreach ($results as $result) {
            if (in_array($result->passed_quiz_id, $quiz_id)) { // отсеивает дубли
                continue;
            }

            $questions_count = Questions::where('quiz_id', $result->passed_quiz_id)->count('id');
            $quiz_id[] = $result->passed_quiz_id;
            $results_message .= "\xF0\x9F\x93\x8C *Название викторины:* _{$result->name}_ \n \xE2\x9C\x85 *Ваше наивысшее число набранных баллов:* _ {$result->total_score} из $questions_count _ \n\n";
        }
        
        $bot->sendMessage($message->getChat()->getId(), trim($results_message), 'markdown');
        $bot->sendMessage($message->getChat()->getId(), 'Если Вы хотите перепройти какую-то из викторин, достаточно просто написать ее название, для возврата в главное меню, напишите /start');
    }
}