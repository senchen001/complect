@extends('layouts.app')

@section('content')
<style>
.complect-item {
    transition: all 0.3s ease;
}
.complect-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}
.complect-number {
    color: white;
    font-weight: bold;
}
.item-counter {
    background-color: #e9ecef;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
}
.item-details {
    max-width: 100%;
}
.item-details .small {
    line-height: 1.4;
}
.fas.fa-barcode, .fas.fa-qrcode {
    width: 16px;
    text-align: center;
}
</style>

<div class="container">
    


    @if(!Session::get('makingComplect'))
        <h1>Сформировать комплект</h1>
        <form action="{{ route('createNewComplect') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Сформировать комплект</button>
        </form>
    @endif

    @if(isset($thisComplect) && !empty($thisComplect))
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> 
                    Состав комплекта № <span class="complect-number">{{ $newComplectNumber ?? 'Неизвестен' }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($thisComplect as $index => $item)
                        <div class="list-group-item complect-item d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                @if(is_array($item))
                                    <div class="item-details">
                                        <div class="mb-1">
                                            <strong class="text-primary">{{ $item['title'] }}</strong>
                                        </div>
                                        <div class="small text-muted">
                                            <span class="me-3">
                                                <i class="fas fa-barcode"></i> 
                                                Инв. №: <strong>{{ $item['invnum'] }}</strong>
                                            </span>
                                            @if(!empty($item['barcode']))
                                                <span>
                                                    <i class="fas fa-qrcode"></i> 
                                                    Штрихкод: <strong>{{ $item['barcode'] }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span>Инв. №: <strong class="text-primary">{{ $item }}</strong></span>
                                @endif
                            </div>
                            @if(Session::get('makingComplect'))
                                <form action="{{ route('removeFromComplect') }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="invnum" value="{{ is_array($item) ? $item['invnum'] : $item }}">
                                    <input type="hidden" name="complID" value="{{ $newComplectNumber }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Удалить {{ is_array($item) ? $item['invnum'] : $item }} из комплекта?')"
                                            title="Удалить из комплекта">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 item-counter">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Всего экземпляров в комплекте: <strong>{{ count($thisComplect) }}</strong>
                    </small>
                </div>
            </div>
        </div>
    @endif

    @if(isset($makingComplect) && Session::get('makingComplect') == true)
        
    
    <h1>Добавить в комплект</h1>
    <form action="{{ route('store') }}" method="POST">
        @csrf
        <div class="form-group">
            <h3>Номер комплекта {{ $newComplectNumber }}</h3>
            <input type="hidden" class="form-control" name="complID" value="{{ $newComplectNumber }}" required>
        </div>
        <div class="form-group">
            <label for="description">Инвентаный номер или штрих-код/RFID экземпляра</label>
            <input type="text" class="form-control" name="invnum" required>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Добавить</button>
    </form>
    <br>
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('closeComplect') }}" method="POST" style="margin-top: 20px;">
        @csrf
        <button type="submit" class="btn btn-danger">Закрыть комплект</button>
    </form>
    @endif
</div>




@endsection