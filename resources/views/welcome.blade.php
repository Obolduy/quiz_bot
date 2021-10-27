{{ $question_text }}
<form method="POST">
    @foreach ($answers as $answer)
    <input type="radio" value="{{$answer->id}}" name="answer"> {{$answer->answer}}
    @endforeach
    <input type="submit" name="submit">
</form>