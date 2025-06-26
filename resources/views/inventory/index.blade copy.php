@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('invFind') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3">Кто проверяет:</div>
            <div class="col-md-3">{{ Auth::user()->name }}</div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="simple-select">База данных:</label>
            </div>
            <div class="col-md-3">
                <select id="db">
                    <option value="1">IBIS</option>
                    <option value="2">DB 1</option>
                    <option value="3">DB 2</option>
                </select>
            </div>
        </div>
        <br>
       <!-- <div class="row">
            <div class="col-md-3">
                <label for="simple-select">Место хранения:</label>
            </div>
            <div class="col-md-3">
                <select id="storLoc">
                    <option value="" selected disabled>Место хранения</option>
                    <option value="1">Хранилище 1</option>
                    <option value="2">Хранилище 1</option>
                    <option value="3">Читальный зал</option>
                </select>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="simple-select">Расстановочный шифр:</label>
            </div>
            <div class="col-md-3">
                <select id="rastShifr">
                    <option value="1">Шифр 1</option>
                    <option value="2">Шифр 2</option>
                    <option value="3">Шифр 3</option>
                </select>
            </div>
        </div>-->
        <br>
        <div class="form-group">
            <label for="description">Инвентаный номер экземпляра</label>
            <input type="text" class="form-control" name="invNum" placeholder="1" required>
        </div>
        <br>
        <div class="form-group">
            <label for="description">Колличество экземпляров</label>
            <input type="text" class="form-control" name="booksNum" required>
        </div>
        <button type="submit" class="btn btn-success">Подтвердить</button>
    </form>
</div>
@endsection