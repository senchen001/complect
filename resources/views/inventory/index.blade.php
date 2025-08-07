@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('invFind') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3">Кто выполняет проверку:</div>
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
                    <option value="HOMELIB">HOMELIB</option>
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
                <select id="storLoc" name="storLoc"> 
                    @foreach($storlocs as $storloc)
                        <option value="{{ $storloc->storloc }}">{{ $storloc->storloc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStorlocModal">
                    Добавить
                </button>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Расстановочный шифр:</label>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="rastShifr" id="rastShifr">
                
            </div>
            <div class="col-md-3">
                
            </div>
        </div>
        <br>
        <div class="form-group">
            <label for="invNum">Инвентаный номер или ш-к экземпляра</label>
            <input type="text" class="form-control" name="invNum" required>
        </div>
        <br>
        <div class="form-group">
            <label for="booksNum">Количество экземпляров</label>
            <input type="text" class="form-control" name="booksNum"  value="1" required>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Найти</button>
    </form>
</div>
<?php
//dd($invStatus);
?>
@if(isset($invStatus))
<div class="container mt-4">
    <h2>Результат проверки</h2>
    <form action="{{ route('approveAccepted') }}" method="POST">
        @csrf
        <div class="row">
            
            <input type="hidden" class="form-control" name="librarian" value="{{ Auth::user()->name }}">
        </div>
        <hr>
        <br>
              
        <div class="row">
            <div class="col-md-3">
                <label for="storLoc">Статус инвентаризации:</label>
            </div>
            <div class="col-md-3">
                                
                <p class="text-success">Дата последней инвентаризации {{ $invDate }}</p>
                                                 
            </div>            
        </div>

        <hr>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Статус экземпляра:</label>
            </div>
            @if($bookStatus['status'] == "U")
            <div class="col-md-3">
                Для ЭК ВУЗа - группа экз-ров (Безинв. учет). Размножение не требуется
            </div>
            @else
            <div class="col-md-3">
                {{ $bookStatus['status'] }} - {{ $bookStatus['statusDescr'] }}
            </div>
            @endif
        </div>
        <hr>

        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Количество экземпляров:</label>
            </div>
            <div class="col-md-3">
                {{ $booksAmount }}
            </div>
        </div>
        <hr>

        <div class="row">
            
            <input type="hidden" class="form-control" name="db" value="{{ $db }}">
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <label for="storLoc">Место хранения:</label>
            </div>
            <div class="col-md-3">
                @if($storLocFound['status'])
                    {{ $storLocFound['storLoc'] }}
                @else
                    <p class="text-danger">место хранения в ИРБИС {{ $storLocFound['storLoc'] }}</p>
                @endif
            </div>
            <input type="hidden" class="form-control" name="storLoc" value="{{ $storLocFound['storLoc'] }}">
        </div>
        <hr>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Расстановочный шифр:</label>
            </div>
            @if($rastShifrFound['status'])
            <div class="col-md-3">
                {{ $rastShifrFound['rastShifr'] }}
            </div>
            @else
            <div class="col-md-3">
                <p class="text-danger">Расстановочный шифр в ИРБИС {{ $rastShifrFound['rastShifr'] }}</p>
            </div>
            @endif
            <input type="hidden" class="form-control" name="rastShifr" value="{{ $rastShifrFound['rastShifr'] }}">
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
                <label for="rastShifr">Штрихкод:</label>
            </div>
            <div class="col-md-3">
                {{ $barcode }}
            </div>
            <input type="hidden" class="form-control" name="barcode" value="{{ $barcode }}">
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
        <br>
        @else
        <input type="hidden" name="booksNum" value="1">
        @endif
        
        
        
        <button type="submit" class="btn btn-success">Инвентаризировать</button>
        
    </form>
    
</div>
@endif
<!-- Модальное окно для добавления нового расстановочного шифра -->
<div class="modal fade" id="addRastshifrModal" tabindex="-1" aria-labelledby="addRastshifrModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRastshifrModalLabel">Добавить новый расстановочный шифр</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRastshifrForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newRastshifr" class="form-label">Расстановочный шифр:</label>
                        <input type="text" class="form-control" id="newRastshifr" name="rastshifr" required>
                        <div class="form-text">Введите новый расстановочный шифр</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления нового места хранения -->
<div class="modal fade" id="addStorlocModal" tabindex="-1" aria-labelledby="addStorlocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStorlocModalLabel">Добавить новое место хранения</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStorlocForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newStorloc" class="form-label">Краткое обозначение:</label>
                        <input type="text" class="form-control" id="newStorloc" name="storloc" required>
                        <div class="form-text">Введите краткое обозначение места хранения (например: Хр1, ЧЗ)</div>
                    </div>
                    <div class="mb-3">
                        <label for="newStorlocdescr" class="form-label">Полное описание:</label>
                        <input type="text" class="form-control" id="newStorlocdescr" name="storlocdescr" required>
                        <div class="form-text">Введите полное описание места хранения (например: Хранилище1, Читальный зал)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка формы расстановочных шифров
    const rastshifrForm = document.getElementById('addRastshifrForm');
    const rastshifrModal = new bootstrap.Modal(document.getElementById('addRastshifrModal'));
    const rastshifrSelect = document.getElementById('rastShifr');
    
    rastshifrForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(rastshifrForm);
        
        fetch('{{ route("rastshifr.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Добавляем новый шифр в select
                const option = new Option(data.rastshifr.rastshifr, data.rastshifr.rastshifr);
                rastshifrSelect.add(option);
                rastshifrSelect.value = data.rastshifr.rastshifr; // Выбираем добавленный шифр
                
                // Очищаем форму и закрываем модальное окно
                rastshifrForm.reset();
                rastshifrModal.hide();
                
                // Показываем сообщение об успехе
                alert('Расстановочный шифр успешно добавлен!');
            } else {
                alert('Ошибка при добавлении шифра: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при добавлении шифра');
        });
    });

    // Обработка формы мест хранения
    const storlocForm = document.getElementById('addStorlocForm');
    const storlocModal = new bootstrap.Modal(document.getElementById('addStorlocModal'));
    const storlocSelect = document.getElementById('storLoc');
    
    storlocForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(storlocForm);
        
        fetch('{{ route("storloc.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Добавляем новое место хранения в select
                const option = new Option(data.storloc.storlocdescr, data.storloc.storloc);
                storlocSelect.add(option);
                storlocSelect.value = data.storloc.storloc; // Выбираем добавленное место
                
                // Очищаем форму и закрываем модальное окно
                storlocForm.reset();
                storlocModal.hide();
                
                // Показываем сообщение об успехе
                alert('Место хранения успешно добавлено!');
            } else {
                alert('Ошибка при добавлении места хранения: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при добавлении места хранения');
        });
    });
});
</script>
@endsection