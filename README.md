<ul>
    <li>PHP: PHP 7.3</li>
    <li>Framework: Laravel 8</li>
    <li>DB: MySQL 8, Redis 5.0</li>
    <li>Server: Apache 2.4</li>
    <li>Requires: <a href="https://github.com/TelegramBot/Api">Telegram Bot Api</a></li>
</ul>

<p>This is a telegram bot using <a href="https://github.com/TelegramBot/Api">Telegram Bot Api</a>.</p>
<p>The idea is that you can add (delete and change too) and pass quizes. User also can adds pictures with questions. The differentiation occurs via "status id" in Redis. The user's Redis DB takes the user's Telegram id for it's name.</p>
<p>List of statuses</p>
<ol>
    <li>User wrote '/start'</li>
    <li>User opened quizes choice</li>
    <li>User passing quiz</li>
    <li>User passed the quiz, showing results, rate</li>
    <li>User creating quiz (Input name)</li>
    <li>User creating quiz (Input questions)</li>
    <li>User creating quiz (Input answers)</li>
    <li>User creating quiz (Choice correct answers)</li>
    <li>User opened quiz chosing and sort it by date</li>
    <li>User opened list of his results</li>
    <li>User opened interaction with his quizes</li>
    <li>User starting to delete his quiz</li>
    <li>User changing quiz (Start)</li>
    <li>User changing quiz (Name)</li>
    <li>User changing quiz (Choose question)</li>
    <li>User changing quiz (Write question)</li>
    <li>User changing quiz (Choose answer)</li>
    <li>User changing quiz (Write answer)</li>
    <li>User changing quiz (Change correct answer (Choose question))</li>
    <li>User changing quiz (Change correct answer (Write answerа))</li>
</ol>
<p>List of comands</p>
<ol>
    <li><b>/start</b> - Bot's main menu</li>
    <li><b>/help</b> - Help</li>
    <li><b>/quiz_list</b> - Show list of all quizes, sorted by rating</li>
    <li><b>/sort_date</b> - Sort /quiz_list by date</li>
    <li><b>/quiz_create</b> - Create quiz</li>
    <li><b>/add_questions_stop</b> - Stop adding questionов</li>
    <li><b>/my_quizes</b> - Show list of created quizes by user</li>
    <li><b>/quiz_delete</b> - Delete quiz</li>
    <li><b>/quiz_change</b> - Change quiz</li>
    <li><b>/change_correct_answer</b> - Change correct answer</li>
    <li><b>/quiz_start</b> - Start selected quiz</li>
    <li><b>/results</b> - Show user's results</li>
    <li><b>/drop_quiz</b> - Stop passing quiz</li>
</ol>
<p>As you can see, I used MySQL 8, but in docker-compose.yml I'm using 5.7. So because of this DB dump using only CREATE TABLE and INSERT commands.</p>

<h2>Run</h2>
<p>You can run this app via docker-compose up and try it on <a href="http://127.0.0.1:8000">http://127.0.0.1:8000</a>. After it you can create telegram bot and set webhook. But better clone this repo, change .env.example to .env and try it via ngrok for example.</p>