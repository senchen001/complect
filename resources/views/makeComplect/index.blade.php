@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Создать комплект</h1>
    <form action="{{ route('store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Название комплекта</label>
            <input type="text" class="form-control" name="complID" required>
        </div>
        <div class="form-group">
            <label for="description">Инвентаный номер экземпляра</label>
            <input type="text" class="form-control" name="invnum" required>
        </div>
        
        <button type="submit" class="btn btn-success">Добавить</button>
    </form>
</div>
@endsection