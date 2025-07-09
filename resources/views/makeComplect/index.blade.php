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
    $makingComplect = Session::get('makingComplect');
    $newComplectNumber = Session::get('newComplectNumber');
    $grouped = [];
    
    // Группируем значения по complNum
    if(isset($complects)){
        foreach ($complects as $item) {
            $value = $item[0]['value'];
            $complNum = $item[0]['complNum'];
            $occurrence = $item[0]['occurrence'];
            $description = $item[0]['description'];
            $status = $item['status'] ?? true; // получаем статус комплекта, по умолчанию доступен
        
            if (!isset($grouped[$complNum])) {
                $grouped[$complNum] = [
                    'status' => $status,
                    'items' => []
                ];
            }
        
            $grouped[$complNum]['items'][] = [
                'value' => $value,
                'occurrence' => $occurrence,
                'description' => $description
            ];
        }
    }
    else{
        $grouped = [];
    }
@endphp

<div class="accordion mb-4" id="complectsAccordion">
@foreach ($grouped as $complNum => $complectData)
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading{{ $complNum }}">
            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $complNum }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $complNum }}">
                <div class="d-flex align-items-center w-100">
                    <span class="me-auto">Комплект: {{ $complNum }}</span>
                    @if($complectData['status'])
                        <span class="badge bg-success ms-2">Доступен для выдачи</span>
                    @else
                        <span class="badge bg-danger ms-2">Выдан читателю</span>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapse{{ $complNum }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $complNum }}" data-bs-parent="#complectsAccordion">
            <div class="accordion-body">
                <ul class="list-group">
                    @foreach ($complectData['items'] as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1">{{ $item['value'] }}</p>
                                <p class="mb-0">{{ $item['description'] }}</p>
                            </div>
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
        </div>
    </div>
@endforeach
</div>
    <h1>Создать комплект</h1>
    <form action="{{ route('createNewComplect') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">Создать комплект</button>
    </form>
    @if(isset($makingComplect) && Session::get('makingComplect') == true)
        @if(isset($thisComplect))
            @foreach($thisComplect as $item)
                <p>{{ $item }}</p>
            @endforeach
        @endif
    
    <h1>Добавить в комплект</h1>
    <form action="{{ route('store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Номер комплекта</label>
            <input type="text" class="form-control" name="complID" value="{{ $newComplectNumber }}" required>
        </div>
        <div class="form-group">
            <label for="description">Инвентаный номер экземпляра</label>
            <input type="text" class="form-control" name="invnum" required>
        </div>
        
        <button type="submit" class="btn btn-success">Добавить</button>
    </form>
    @endif
</div>
</div>




@endsection