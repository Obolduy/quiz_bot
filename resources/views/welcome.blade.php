<form method="POST" enctype="multipart/form-data">
    @csrf
    <input type="text" name="text">
    <div class="photo">Добавьте изображение: <input type="file" accept="image/*" name="photo"></div>
    <div><input type="submit" name="submit"></div>
</form>