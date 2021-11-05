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

class TestController extends Controller
{
    public static $id = 12;

    public function test(Request $request)
    {   
        $page = 1;

        $pageFrom = ($page * 5) - 5;
        $pageTo = 5;
        // $quizes = DB::table('quizes')->select('* limit ?, ?', [$pageFrom, $pageTo])->get();
        $quizes = DB::raw("select * from quizes limit $pageFrom, $pageTo");
        $quizes = DB::table('quizes')
                ->offset($pageFrom)
                ->limit($pageTo)
                ->get();

        var_dump($quizes);
    }  
}