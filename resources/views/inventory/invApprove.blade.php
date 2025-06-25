@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('approveAccepted') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3">Кто проверяет:</div>
            <div class="col-md-3">{{ Auth::user()->name }}</div>
            <input type="hidden" class="form-control" name="librarian" value="{{ Auth::user()->name }}">
        </div>
        <br>
       
        
        <div class="row">
            <div class="col-md-3">
                <label for="storLoc">Место хранения:</label>
            </div>
            <div class="col-md-3">
                {{ $storLocFound }}
            </div>
            <input type="hidden" class="form-control" name="storLoc" value="{{ $storLocFound }}">
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Расстановочный шифр:</label>
            </div>
            <div class="col-md-3">
                {{ $rastShifrFound }}
            </div>
            <input type="hidden" class="form-control" name="rastShifr" value="{{ $rastShifrFound }}">
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Инвентарный номер:</label>
            </div>
            <div class="col-md-3">
                {{ $invNum }}
            </div>
            <input type="hidden" class="form-control" name="invNum" value="{{ $invNum }}">
        </div>
        <br>
        <div class="form-group">
            <label for="booksNum">Количество экземпляров</label>
            <input type="text" class="form-control" name="booksNum" value="1">
        </div>
        <div class="row">
            {{ $bookDescr }}
        </div>
        <br>
        <button type="submit" class="btn btn-success">Подтвердить</button>
    </form>
</div>
@endsection