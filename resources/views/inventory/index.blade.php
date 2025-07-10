@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Проверка фонда</h1>
    <form action="{{ route('invFind') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3">Кто проверяет:</div>
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
                    
                    <option value="Хранилище1">Хранилище1</option>
                    <option value="Хранилище2">Хранилище2</option>
                    <option value="Читальный_зал">Читальный_зал</option>
                </select>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Расстановочный шифр:</label>
            </div>
            <div class="col-md-3">
                <select id="rastShifr" name="rastShifr"> 
                    @foreach($rastshifrs as $rastshifr)
                        <option value="{{ $rastshifr->rastshifr }}">{{ $rastshifr->rastshifr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRastshifrModal">
                    Добавить
                </button>
            </div>
        </div>
        <br>
        <div class="form-group">
            <label for="invNum">Инвентаный номер экземпляра</label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addRastshifrForm');
    const modal = new bootstrap.Modal(document.getElementById('addRastshifrModal'));
    const select = document.getElementById('rastShifr');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
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
                select.add(option);
                select.value = data.rastshifr.rastshifr; // Выбираем добавленный шифр
                
                // Очищаем форму и закрываем модальное окно
                form.reset();
                modal.hide();
                
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
});
</script>
@endsection