<?php

namespace App\Http\Controllers;

use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;

class ShowUserQuizesController extends Controller
{
    public function showQuizes($message, $bot)
    {
        $id = $message->getChat()->getId();

        $quizes = Quizes::where('creator_id', $id)->get();

        $quiz_list = '';
        foreach ($quizes as $quiz) {
            $quiz_list .= $quiz->name;
        }

        Redis::hmset($id, 'status_id', '2');
        $bot->sendMessage($id, $quiz_list);
    }
}
