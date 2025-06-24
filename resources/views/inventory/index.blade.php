@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('invApprove') }}" method="POST">
        @csrf
        <div class="form-group">
        <label for="simple-select">Кто проверяет:</label>
            <select id="librarian">
                <option value="" selected disabled>-- Библиотекарь --</option>
                <option value="1">Иванов</option>
                <option value="2">Петров</option>
                <option value="3">Сидоров</option>
            </select>
        </div>
        <div class="form-group">
        <label for="simple-select">Место хранения:</label>
            <select id="librarian">
                <option value="" selected disabled>-- Место хранения --</option>
                <option value="1">Хранилище 1</option>
                <option value="2">Хранилище 1</option>
                <option value="3">Читальный зал</option>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Инвентаный номер экземпляра</label>
            <input type="text" class="form-control" name="invnum" required>
        </div>
        
        <button type="submit" class="btn btn-success">Добавить</button>
    </form>
</div>
@endsection