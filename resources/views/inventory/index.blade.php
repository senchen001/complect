@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('invApprove') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3">Кто проверяет:</div>
            <div class="col-md-3">{{ Auth::user()->name }}</div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="simple-select">Место хранения:</label>
            </div>
            <div class="col-md-3">
                <select id="librarian">
                    <option value="" selected disabled>Место хранения</option>
                    <option value="1">Хранилище 1</option>
                    <option value="2">Хранилище 1</option>
                    <option value="3">Читальный зал</option>
                </select>
            </div>
        </div>
        <br>
        <div class="form-group">
            <label for="description">Инвентаный номер экземпляра</label>
            <input type="text" class="form-control" name="invnum" required>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Подтвердить</button>
    </form>
</div>
@endsection