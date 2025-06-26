@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('invFind') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3">Кто проверяет:</div>
            <div class="col-md-3">
            <?php
            if(isset(Auth::user()->name)){
                echo Auth::user()->name;
            }else {
                header("Location: /login");
                exit(); // Не забудьте вызвать exit() после редиректа
            }
            ?>    
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="db">База данных:</label>
            </div>
            <div class="col-md-3">
                <select id="db" name="db"> <!-- Добавлен атрибут name -->
                    <option value="IBIS">IBIS</option>
                    <option value="DB1">DB1</option>
                    <option value="DB2">DB2</option>
                </select>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="storLoc">Место хранения:</label>
            </div>
            <div class="col-md-3">
                <select id="storLoc" name="storLoc"> <!-- Добавлен атрибут name -->
                    
                    <option value="Хранилище1">Хранилище1</option>
                    <option value="Хранилище2">Хранилище2</option>
                    <option value="Читальный_зал">Читальный_зал</option>
                </select>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Расстановочный шифр:</label>
            </div>
            <div class="col-md-3">
                <select id="rastShifr" name="rastShifr"> <!-- Добавлен атрибут name -->
                    <option value="1">Шифр 1</option>
                    <option value="2">Шифр 2</option>
                    <option value="3">Шифр 3</option>
                </select>
            </div>
        </div>
        <br>
        <div class="form-group">
            <label for="invNum">Инвентаный номер экземпляра</label>
            <input type="text" class="form-control" name="invNum" placeholder="1" required>
        </div>
        <br>
        <div class="form-group">
            <label for="booksNum">Количество экземпляров</label>
            <input type="text" class="form-control" name="booksNum" required>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Найти</button>
    </form>
</div>
@endsection