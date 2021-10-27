<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test(Request $request)
    {
        // он должен проверять был ли вопрос уже задан пользователю
        // в current_user_quiz пишется на какие вопросы юзер ответил
        // берутся каждый раз все вопросы из таблицы с определенным quiz_id и перебираются
        // и проверка есть ли айди этого вопроса в current_user_quiz рядом с айди пользователя
        // если нет - задаем, да - скипаем
        // либо создать таблицу пройденных ВОПРОСОВ а не ответов
        
        $quiz_id = 1;
        $user_id = 1;
        
        if ($request->isMethod('GET')) {
            $questions = DB::table('questions')
                        ->select('*')
                        ->where('quiz_id', '=', $quiz_id)
                        ->get();

            $question_text = '';

            foreach ($questions as $question) {
                $passed_questions = DB::table('current_user_quiz')
                                    ->select('passed_question_id')
                                    ->where('user_id', '=', 1)
                                    ->where('passed_question_id', '=', $question->id)
                                    ->first();

                if (!$passed_questions) {
                    $question_text = $question->question;
                    $answers = DB::table('answers')
                                ->select('answer', 'id')
                                ->where('question_id', '=', $question->id)
                                ->get();

                    break;
                }
            }

            if ($question_text == '') {
                $score = 0;

                $current_user_quiz = DB::table('current_user_quiz')
                                ->select('*')
                                ->where('user_id', '=', $user_id)
                                ->where('quiz_id', '=', $quiz_id)
                                ->get();
                
                foreach ($current_user_quiz as $elem) {
                    $correct_answers = DB::table('correct_answers')
                                        ->select('*')
                                        ->where('question_id', '=', $elem->passed_question_id)
                                        ->first();

                    if ($correct_answers->answer_id == $elem->passed_answer_id) {
                        $score++;
                    }
                }

                DB::table('passed_quizes')->insert([
                    'passed_quiz_id' => $quiz_id, 'user_id' => $user_id,
                    'total_score' => $score
                ]);
                
                echo "Колличество набранных Вами баллов: $score";
            }

            return view('welcome', ['question_text' => $question_text, 'answers' => $answers]);
        }

        $passed_question_id = DB::table('answers')
                                ->select('question_id')
                                ->where('id', '=', $request->answer)
                                ->first();

        DB::table('current_user_quiz')->insert([
            'quiz_id' => $quiz_id, 'user_id' => $user_id,
            'passed_question_id' => $passed_question_id->question_id, 'passed_answer_id' => $request->answer
        ]);

        return redirect('/biba');
    }
}
