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
        <hr>
        <br>
              
        <div class="row">
            <div class="col-md-3">
                <label for="storLoc">Статус инвентаризации:</label>
            </div>
            <div class="col-md-3">
                
                @if($invStatus)
                    <p class="text-success">экземпляр прошел инвентаризацию</p>
                @else
                    <p class="text-danger">экземпляр не прошел инвентаризацию</p>
                @endif   
            </div>            
        </div>

        <hr>

        <div class="row">
            <div class="col-md-3">
                <label for="storLoc">База данных:</label>
            </div>
            <div class="col-md-3">
                {{ $db }}
            </div>
            <input type="hidden" class="form-control" name="db" value="{{ $db }}">
        </div>
        <hr>
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
        <hr>
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
        <hr>
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
        <hr>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Экземпляр:</label>
            </div>
            <div class="col-md-3">
                {{ $bookDescr }}
            </div>
            <input type="hidden" class="form-control" name="bookDescr" value="{{ $bookDescr }}">
        </div>

        @if(!$invStatus)
        <div class="form-group">
            <label for="booksNum">Количество экземпляров</label>
            <input type="text" class="form-control" name="booksNum" value="1">
        </div>
        @endif
        
        
        @if(!$invStatus)
            <button type="submit" class="btn btn-success">Инвентаризировать</button>
        @endif
    </form>
    @if($invStatus)
        <a href="/inventory" class="btn btn-primary">Перейти в Инвентаризацию</a>
    @endif
</div>
@endsection