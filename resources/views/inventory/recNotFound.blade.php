@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Запись {{ $invNum }} не найдена</h2>
    <a href="/inventory" class="btn btn-primary">Перейти в Инвентаризацию</a>
</div>
@endsection