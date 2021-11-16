<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\CurrentUserQuiz;
use App\Models\PassedQuizes;
use App\Models\Questions;
use App\Models\Quizes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        echo asset('questions/d6336ce5bfdf9d1ff7a82888cb42fca7.jpg'); die();
        // file_put_contents('testfile.png', 'qwer.png'); die();
        // $link = Storage::put("questions", 'qwer.png');
        // var_dump($link); die();

        // $id = 810293946;

        // if ($request->isMethod('GET')) {
        //     return view('welcome');
        // }

        // $test = Storage::put('questions', $request->photo);

        // var_dump($request->photo);
        // echo asset($test); die();

        $photo_id = 'AgACAgIAAxkBAAITRGGTdcG6_2-1XuSQYQTerHM9oDtNAAIstzEbZh-ZSM_r2uZluz5WAQADAgADeQADIgQ';
        $curl = curl_init('https://api.telegram.org/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/getFile');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, ['file_id' => $photo_id]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $res = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($res, true);

        if ($res['ok']) {
            $matches = [];
            preg_match('#\.(.+)$#u', $res['result']['file_path'], $matches);

            $src  = 'https://api.telegram.org/file/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/'.$res['result']['file_path'];
            $rename = md5(time() . basename($src)) . '.' .$matches[1];

            copy($src, "questions/$rename");

            // $link = asset($link);

            // sleep(4);
            
            // $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
            // $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaPhoto($link));
            // $bot->sendMediaGroup($id, $media);
        }
    }  
}