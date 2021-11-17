<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\CurrentUserQuiz;
use App\Models\PassedQuizes;
use App\Models\QuestionPictures;
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
        $questions = Questions::where('quiz_id', 41)->get();

        foreach ($questions as $question) {
            $questions_list[] = $question->question;

            $picture = QuestionPictures::where('question_id', $question->id)->value('picture');

            $questions_pictures = [];
            if ($picture) {
                $questions_pictures[] = $picture;
            }
        }

        var_dump($questions_pictures);
    }  
}