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
        $status = $item['status'] ?? true; // получаем статус комплекта, по умолчанию доступен
        
        if (!isset($grouped[$complNum])) {
            $grouped[$complNum] = [
                'status' => $status,
                'items' => []
            ];
        }
        
        $grouped[$complNum]['items'][] = [
            'value' => $value,
            'occurrence' => $occurrence
        ];
    }
    
@endphp

@foreach ($grouped as $complNum => $complectData)
    <div class="mb-4">
        <h3 class="d-flex align-items-center">
            Комплект: {{ $complNum }}
            @if($complectData['status'])
                <span class="badge bg-success ms-2">Доступен для выдачи</span>
            @else
                <span class="badge bg-danger ms-2">Выдан читателю</span>
            @endif
        </h3>
        <ul class="list-group mb-3">
            
            @foreach ($complectData['items'] as $item)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $item['value'] }}</span>
                    @if($complectData['status'])
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
                    @endif
                </li>
            @endforeach
            
        </ul>
    </div>
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