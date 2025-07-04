@extends('layouts.app')

@section('content')
<div class="container">
    @php
    
    $grouped = [];
    
    // Группируем значения по complNum
    foreach ($complects as $item) {
        $value = $item[0]['value'];
        $complNum = $item[0]['complNum'];
        
        if (!isset($grouped[$complNum])) {
            $grouped[$complNum] = [];
        }
        
        $grouped[$complNum][] = $value;
    }
    
@endphp

@foreach ($grouped as $complNum => $values)
    <h3>Комплект: {{ $complNum }}</h3>
    <ul>
        @foreach ($values as $value)
            <li>{{ $value }}</li>
        @endforeach
    </ul>
@endforeach
    <h1>Добавить в комплект</h1>
    <form action="{{ route('store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Номер комплекта</label>
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