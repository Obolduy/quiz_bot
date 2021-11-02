<?php

namespace App\Http\Controllers;

use App\Models\Answers;
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

    public function createQuizQuestion($update, $bot)
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

        $bot->sendMessage($message->getChat()->getId(),
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
            $bot->sendMessage($message->getChat()->getId(),
                "Мы закончили с ответами! Самое время выбрать правильные ответы!
                    Сейчас Вам по порядку будут отправлены ответы на вопросы, Ваша задача - отметить правильный.
                        Сделать это нужно, отправив цифру, соответствующую варианту ответа.
                            Напишите 'Готов', чтобы начать!"); // аналогично ниже
        }

        $bot->sendMessage($message->getChat()->getId(),
            "Ответ принят!"); // над текстом (выводом вопроса) подумай
    }

    public function createQuizCorrectAnswers($message, $bot)
    {
        $id = $message->getChat()->getId();
        $message_text = trim(strip_tags($message->getText()));

        $correct_answers = Redis::hgetall($id."_create_correct_answers_question"); // получаем правильные ответы, если они записаны

        $questions = Redis::hgetall($id."_create_quiz"); // получаем вопросы

        $count = 1; // нумерация с единицы, т.к. нулевой элемент в массиве - название квиза
        foreach ($questions as $key => $value) {
            if ($key != 'quiz_name') {
                // пишем в массив ответы ко всем вопросам и их порядковые номера, как номер ответа=>ответ
                $answers[$count] = Redis::hgetall($id."_create_answers_question_$count");
                $count++;
            }
        }

        $variables = '';
        foreach ($answers as $number => $answer) {
            /* количество элементов массива правильных ответов равняется числу уже "пройденных" циклом
            вопросов, потому прибавляем к этому числу единицу, дабы записать в новый элемент новый ответ */
            if ((count($correct_answers) + 1) == $number) {
                for ($i = 1; $i <= count($answer); $i++) {
                    $variables .= "$i. ". $answer["answer_$i"]."\n"; // записываем вопросы как номер. вопрос
                }

                $bot->sendMessage($id, $variables); // над выводом ответов подумай

                if ($message_text !== strtolower('готов') && !in_array($message_text, range(1, 4))) {
                    Redis::hset($id."_create_correct_answers_question", "question_$number", $message_text);
                }
            }
        }

        if (count($correct_answers) == count($answers)) {
            $bot->sendMessage($id, "Поздравляем! Ваша викторина успешно создана"); // Добавляем в базу все и даем ссылку на викторину
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
                $questions[] = Questions::create([
                    'quiz_id' => $quiz->id,
                    'question' => Redis::hget($id."_create_quiz", "question_$i")
                ]);
            }
        });
    }
}