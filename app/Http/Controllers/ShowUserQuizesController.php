<?php

namespace App\Http\Controllers;

use App\Models\{Quizes, QuizStars};
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\{Message, ReplyKeyboardMarkup, Update};

class ShowUserQuizesController extends Controller
{
    /**
     * Shows list of user's quizes
     * @param Message
     * @param Client
     * @return void
     */

    public function showUserQuizes(Message $message, Client $bot): void
    {
        $id = $message->getChat()->getId();

        $page = Redis::hget($id, 'page') ?? 1;

        if ($page === 1) {
            $bot->sendMessage($id,
                "Чтобы *отсортировать викторины* по _дате добавления_, введите /sort\_date, чтобы *сортировать* _как обычно_ - /my\_quizes", 'markdown');
        }

        $quizes = (new PaginationController)->paginateQuiz($id, $page, ['creator_id', $id]);

        Redis::hmset($id, 'status_id', '11');

        $quizes_count = 0;
        foreach ($quizes as $quiz) {
            $quiz_list[] = ['Викторина ' . ++$quizes_count];

            Redis::hset($message->getChat()->getId().'_quizes_pagination', $quizes_count, $quiz->name);
        }

        if ((int)$page !== 1) {
            $quiz_list[] = ['Назад'];
        }

        if (count($quizes) >= 5) {
            $quiz_list[] = ['Далее'];
        }

        $keyboard = new ReplyKeyboardMarkup($quiz_list, true);

        $bot->sendMessage($id, 'Выберите викторину, чтобы начать с ней взаимодействие', null, false, null, $keyboard);
    }

    /**
     * Selects quiz and waits for commands
     * @param Update
     * @param Client
     * @return void
     */

    public function selectUserQuiz(Update $update, Client $bot): void
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        if (!in_array(mb_strtolower($message_text, 'UTF-8'), ['далее', 'назад'])) {
            $message_text = preg_replace('#\D#u', '', $message_text);

            $quizes = Redis::hgetall($id.'_quizes_pagination');

            foreach ($quizes as $number => $question_name) {
                if ($message_text == $number) {
                    $message_text = $question_name;
                }
            }
        }

        $quiz = Quizes::where('name', $message_text)->where('creator_id', $id)->first();

        if ($quiz) {
            Redis::hmset($id, "quiz_id", $quiz->id);

            $stars = $this->getQuizStars($quiz->id);

            $bot->sendMessage($id, "\xE2\x9C\x85 Вы выбрали Вашу викторину *{$quiz->name}*.\n\xE2\xAD\x90 $stars\n\xF0\x9F\x93\x9D Чтобы *отредактировать викторину*, напишите /quiz\_change\n\xE2\x9D\x8E Чтобы *удалить викторину*, напишите /quiz\_delete\n\xE2\x9D\x93 Чтобы *пройти викторину самостоятельно*, напишите /quiz\_start", 'markdown');
        } else if ($message_text == 'Далее') {
            (int)$page = Redis::hget($id, 'page') ?? 1;
            Redis::hset($id, 'page', ++$page);

            $this->showUserQuizes($message, $bot);
        } else if ($message_text == 'Назад') {
            (int)$page = Redis::hget($id, 'page') ?? 1;
            Redis::hset($id, 'page', --$page);

            $this->showUserQuizes($message, $bot);  
        } else {
            $bot->sendMessage($id, 'Уточните название викторины');
        }
    }

    /**
     * Get quiz rating by id
     * @param int quiz id
     * @return string quiz rating
     */

    private function getQuizStars(int $quiz_id): string
    {
        $stars = QuizStars::where("quiz_id", $quiz_id)->first();
        $stars_avg = $stars->stars_avg;
        $votes_count = $stars->votes_count;

        $mark_text = '';
        switch (substr($votes_count, -1)) {
            case '1':
                $mark_text = 'оценки';
                break;
            default:
                $mark_text = 'оценок';
        }

        $stars_pic = '';
        for ($i = 1; $i <= $stars_avg; $i++) {
            $stars_pic .= " \xE2\xAD\x90";
        }

        switch ($stars_avg) {
            case '1':
                $stars_avg .= ' балл';
                break;
            case '5':
                $stars_avg .= ' баллов';
                break;
            default:
                $stars_avg .= ' балла';
        }

        $stars_text = ($votes_count && $stars_avg) ? 
            "Средняя оценка: $stars_pic _($stars_avg)_ на основе $votes_count $mark_text." :
            "Пока никто не оценил Вашу викторину.";

        return $stars_text;
    }
}