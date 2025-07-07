@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @php
    
    $grouped = [];
    
    // Группируем значения по complNum
    foreach ($complects as $item) {
        $value = $item[0]['value'];
        $complNum = $item[0]['complNum'];
        $occurrence = $item[0]['occurrence'];
        
        if (!isset($grouped[$complNum])) {
            $grouped[$complNum] = [];
        }
        
        $grouped[$complNum][] = [
            'value' => $value,
            'occurrence' => $occurrence
        ];
    }
    
@endphp

@foreach ($grouped as $complNum => $values)
    <h3>Комплект: {{ $complNum }}</h3>
    <ul class="list-group mb-3">
        @foreach ($values as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $item['value'] }}</span>
                <form action="{{ route('removeFromComplect') }}" method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этот экземпляр из комплекта?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="complID" value="{{ $complNum }}">
                    <input type="hidden" name="invnum" value="{{ $item['value'] }}">
                    <input type="hidden" name="occurrence" value="{{ $item['occurrence'] }}">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Удалить
                    </button>
                </form>
            </li>
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