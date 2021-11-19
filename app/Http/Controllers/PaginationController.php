<?php

namespace App\Http\Controllers;

use Countable;
use App\Models\Quizes;
use Illuminate\Support\Facades\Redis;

class PaginationController extends Controller
{
    /**
     * Paginate quizes by date or average rating (differentiating by status_id)
     * @param int user's id
     * @param int page number
     * @param null|array 'where' array like [field, value]
     * @return void
     */

    public function paginateQuiz(int $id, int $page, ?array $whereCase): Countable
    {
        $pageFrom = ($page * 5) - 5; // вывод по 5 квизов
        $pageTo = 5;

        $whereField = $whereCase[0] ?? null;
        $whereValue = $whereCase[1] ?? null;

        if (Redis::hget($id, 'status_id') == '9') { // сортировка по дате добавления (id)
            $quizes = Quizes::offset($pageFrom)
                        ->where($whereField, $whereValue)
                        ->orderBy('id', 'desc')
                        ->limit($pageTo)
                        ->get();

            if (!$quizes) {
                $quizes = Quizes::offset($page)
                        ->where($whereField, $whereValue)
                        ->orderBy('id', 'desc')
                        ->limit($pageTo)
                        ->get();
            }
        } else { // сортировка по средней оценке
            $quizes = Quizes::select('quizes.*', 'quiz_stars.stars_avg')
                ->where($whereField, $whereValue)
                ->offset($pageFrom)
                ->leftJoin('quiz_stars', 'quizes.id', '=', 'quiz_stars.quiz_id')
                ->orderBy('quiz_stars.stars_avg', 'desc')
                ->limit($pageTo)
                ->get();

            if (!$quizes) {
                $quizes = Quizes::select('quizes.*', 'quiz_stars.stars_avg')
                        ->where($whereField, $whereValue)
                        ->offset($page)
                        ->leftJoin('quiz_stars', 'quizes.id', '=', 'quiz_stars.quiz_id')
                        ->orderBy('quiz_stars.stars_avg', 'desc')
                        ->limit($pageTo)
                        ->get();
            }
        }

        return $quizes; 
    }
}
