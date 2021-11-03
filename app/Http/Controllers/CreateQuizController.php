<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\CorrectAnswers;
use App\Models\Questions;
use App\Models\Quizes;
use Illuminate\Support\Facades\{Redis, DB};

class CreateQuizController extends Controller
{
    public function createQuizStart($message, $bot)
    {
        Redis::hmset($message->getChat()->getId(), 'status_id', '5');

        $bot->sendMessage($message->getChat()->getId(),
            'Введите название Вашей викторины, не превышающее 64 символов! 
                Пожалуйста, будьте корректны в выборе наименования:)');
    }

    public function createQuizName($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        if (Quizes::where('name', $message_text)->first()) {
            $bot->sendMessage($id, 'Данное название уже используется, пожалуйста, придумайте другое');
        } else {
            Redis::hmset($id, 'status_id', '6');
            Redis::hset($id."_create_quiz", 'quiz_name', $message_text);

            $bot->sendMessage($id, 'Отлично, теперь пора придумывать вопросы! Введите и отправьте свой вопрос');
        }
    }

    public function createQuizQuestions($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_array = str_split(trim(strip_tags($message->getText())));

        if (!in_array('?', $message_array)) {
            array_push($message_array, '?');
        }

        $question = implode('', $message_array);

        $question_number = count(Redis::hgetall($id."_create_quiz"));
        $question_number = ($question_number == 1) ? 1 : $question_number; // нумеровать вопросы с единицы

        Redis::hset($id."_create_quiz", "question_$question_number", $question);

        $bot->sendMessage($id, 'Вопрос добавлен! Введите следующий или, если хотите закончить ввод, напишите "/add_questions_stop"');
    }

    public function createQuizAnswerStart($message, $bot)
    {
        $id = $message->getChat()->getId();
        Redis::hmset($id, 'status_id', '7');

        $questions = Redis::hgetall($id."_create_quiz");

        $count = 1; // нумерация с единицы
        foreach ($questions as $key => $value) {
            if ($key != 'quiz_name') { // создает столько таблиц, сколько вопросов
                Redis::hset($id."_create_answers_question_$count", "answer_1", 'empty');
                $count++;
            }
        }

        $bot->sendMessage($id,
            "Мы закончили с вопросами! Теперь пора придумывать ответы на них. 
            Введите ответ и отправьте его, после 4-го варианта Вы будете автоматически переведены 
            на следующий вопрос. Правильные ответы Вы сможете пометить чуть позже.
            Для начала, введите первый ответ на Ваш вопрос \"{$questions['question_1']}\"");
    }

    public function createQuizAnswers($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $questions_count = Redis::hgetall($id."_create_quiz"); // получаем все вопросы

        $is_done = false; // проверка, закончено ли добавление ответов
        for ($i = 1; $i < count($questions_count); $i++) { // нумерация, начиная с первого вопроса
            $question = Redis::hgetall($id."_create_answers_question_$i"); // получаем все ответы вопроса

            $answer_count = count($question);

            $question_text_number = ($answer_count !== 3) ? $i : $i + 1;
            $question_text = Redis::hget($id."_create_quiz", "question_$question_text_number"); // получаем текст вопроса

            // если это последний ответ последнего вопроса, добавление ответов закончено
            if ($answer_count == 3 && $i == (count($questions_count) - 1)) {
                $is_done = true;
            }

            if ($question["answer_1"] == 'empty') { // т.к первый ответ всегда 'empty', перезаписываем его
                Redis::hset($id."_create_answers_question_$i", "answer_1", $message_text);

                break;
            }

            if ($answer_count < 4) { // количество ответов всегда 4
                $answer_count++;
                Redis::hset($id."_create_answers_question_$i", "answer_$answer_count", $message_text);

                break;
            }
        }

        if ($is_done) {
            Redis::hmset($id, 'status_id', '8');
            $bot->sendMessage($id,
                "Мы закончили с ответами! Самое время выбрать правильные ответы!
                    Сейчас Вам по порядку будут отправлены ответы на вопросы, Ваша задача - отметить правильный.
                        Пожалуйста, напишите номера правильных вариантов ответа одним сообщением.
                            Напишите 'Готов', чтобы начать!");
        } else {
            $bot->sendMessage($id, "Ответ принят! Вопрос: \"$question_text\"");
        }
    }

    public function createQuizCorrectAnswers($update, $bot)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $questions = Redis::hgetall($id."_create_quiz"); // получаем вопросы

        for ($i = 1; $i < count($questions); $i++) {
            // пишем в массив ответы ко всем вопросам и их порядковые номера, как номер ответа=>ответ
            $answers[$i] = Redis::hgetall($id."_create_answers_question_$i");
        }

        $message_array = str_split(str_replace([' ', ',', '.'], '', (int)$message_text));

        if (count($answers) == count($message_array)) {
            for ($i = 0; $i <= count($message_array); $i++) {
                $number = $i + 1;
                if ($number <= count($message_array)) {
                    Redis::hset($id."_create_correct_answers_question", "question_$number", $message_array[$i]);
                }
            }

            $this->createQuizDone($id);
            Redis::hmset($id, 'status_id', '1');

            $bot->sendMessage($id, "Поздравляем! Ваша викторина успешно создана,
                Вы можете посмотреть все созданные викторины с помощью команды /my_quizes");
        } else {
            $variables = '';

            foreach ($answers as $number => $answer) {
                $variables .= $questions["question_$number"] . "\n";
                for ($i = 1; $i <= count($answer); $i++) {
                    $variables .= "$i. ". $answer["answer_$i"]."\n"; // записываем ответы как номер. ответ
                }
            }
            $bot->sendMessage($id, $variables);
        }
    }

    public function createQuizDone($id)
    {
        DB::transaction(function () use ($id) {
            $quiz = Quizes::create([
                'name' => Redis::hget($id."_create_quiz", 'quiz_name'),
                'creator_id' => $id
            ]);

            for ($i = 1; $i < count(Redis::hgetall($id."_create_quiz")); $i++) {
                $question = Questions::create([
                    'quiz_id' => $quiz->id,
                    'question' => Redis::hget($id."_create_quiz", "question_$i")
                ]);

                $answers_list[$question->id] = Redis::hgetall($id."_create_answers_question_$i");
            }
            
            foreach ($answers_list as $question_id => $answers) {
                foreach ($answers as $answer) {
                    $all_answers[] = Answers::create([
                        'question_id' => $question_id,
                        'answer' => $answer
                    ]);
                }
            }

            for ($i = 1; $i <= count(Redis::hgetall($id."_create_correct_answers_question")); $i++) {
                $correct_answer = Redis::hget($id."_create_correct_answers_question", "question_$i");
                $correct_answers_array[] = Redis::hget($id."_create_answers_question_$i", $correct_answer);
            }

            foreach ($correct_answers_array as $elem) {
                foreach ($all_answers as $answers_text) {
                    if ($answers_text->answer == $elem) {
                        CorrectAnswers::create([
                            'question_id' => $answers_text->question_id,
                            'answer_id' => $answers_text->id
                        ]);
                    }
                }
            }
        });

        for ($i = 1; $i < count(Redis::hgetall($id."_create_quiz")); $i++) {
            Redis::del($id."_create_answers_question_$i");
        }

        Redis::del($id."_create_quiz");
        Redis::del($id."_create_correct_answers_question");
    }
}