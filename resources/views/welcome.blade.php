<div>Вопрос: {{$question_text}}</div>
<div>
    @foreach ($answers as $answer)
    {{$answer->answer}} <br>
    @endforeach
</div>
<form method="POST">
    <input type="text" name="text">
    <input type="submit" name="submit">
</form>