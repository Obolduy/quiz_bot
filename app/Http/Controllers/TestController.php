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
        $quiz_id = 1;
        $user_id = 810293946;
        
        if ($request->isMethod('GET')) {
            $questions = Questions::where('quiz_id', $quiz_id)->get();

            $question_text = '';

            foreach ($questions as $question) {
                $passed_questions = CurrentUserQuiz::where('user_id', $user_id)
                                    ->where('passed_question_id', $question->id)
                                    ->first();

                if (!$passed_questions) {
                    $question_text = $question->question;// id вопроса еще стоит записать куда нибудь может прям в базу кстати говоря раз на то пошло

                    $answers = Answers::where('question_id', $question->id)->get();

                    CurrentUserQuiz::create([
                        'quiz_id' => $quiz_id,
                        'user_id' => $user_id,
                        'passed_question_id' => $question->id
                    ]);

                    break;
                }
            }

            if ($question_text == '') {
                $score = 0;

                $current_user_quiz = CurrentUserQuiz::where('user_id', '=', $user_id)
                                    ->where('quiz_id', '=', $quiz_id)->get();
                
                foreach ($current_user_quiz as $elem) {
                    $correct_answers = CorrectAnswers::where('question_id', $elem->passed_question_id)
                                        ->first();


                    if ($correct_answers->answer_id == $elem->passed_answer_id) {
                        $score++;
                    }
                }

                PassedQuizes::create([
                    'passed_quiz_id' => $quiz_id, 
                    'user_id' => $user_id,
                    'total_score' => $score
                ]);
                
                echo "Колличество набранных Вами баллов: $score";
            }

            return view('welcome', ['question_text' => $question_text, 'answers' => $answers]);
        }

        $current_user_quiz = CurrentUserQuiz::where('user_id', $user_id)->orderByDesc('id')->first();

        $answer = Answers::where('question_id', $current_user_quiz->passed_question_id)->
            where('answer', $request->text)->first();

        $current_user_quiz->passed_answer_id = $answer->id;
        $current_user_quiz->save();

        return redirect('/biba');
    }  
}