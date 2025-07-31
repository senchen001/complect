@extends('layouts.app')

@section('content')
<script src="js/jquery-3.5.1.slim.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/bootstrap-datepicker.ru.min.js"></script>

<style>
    /* Стилизация для выделения текущей даты */
    .datepicker table tr td.today {
        border: 2px solid #007bff !important;
        border-radius: 3px;
        background-color: #e3f2fd !important;
        font-weight: bold;
    }
    
    .datepicker table tr td.today:hover {
        border: 2px solid #0056b3 !important;
        background-color: #bbdefb !important;
    }
</style>

<script>
    $(document).ready(function() {
        // Настройка видимого календаря
        $('#datepicker-preview').datepicker({
            format: 'dd.mm.yyyy', // Формат даты
            language: 'ru', // Язык
            autoclose: true, // Закрытие после выбора даты
            todayHighlight: true // Подсветка текущей даты
        });
        
        // Инициализация значений из сессии
        var sessionDate = '{{ session("returnDate") }}';
        if (sessionDate) {
            $('#datepicker-preview').val(sessionDate);
            $('#datepicker').val(sessionDate);
            updateHiddenDateFields();
        }
        
        // Инициализация места выдачи из сессии
        var sessionPickupLocation = '{{ session("pickupLocation") }}';
        if (sessionPickupLocation) {
            $('#pickupLocation').val(sessionPickupLocation);
        } else {
            // Устанавливаем значение по умолчанию, если нет сохраненного
            $('#pickupLocation').val('lib1');
        }
        
        // Инициализация скрытого поля места выдачи (после восстановления из сессии)
        updateHiddenPickupLocation();
        
        // Функция для обновления всех скрытых полей с датой
        function updateHiddenDateFields() {
            var dateValue = $('#datepicker-preview').val();
            $('#reader-form-date').val(dateValue);
            $('#search-form-date').val(dateValue);
        }
        
        // Функция для обновления скрытых полей места выдачи
        function updateHiddenPickupLocation() {
            var pickupLocationValue = $('#pickupLocation').val();
            $('#search-form-pickup-location').val(pickupLocationValue);
            $('#reader-form-pickup-location').val(pickupLocationValue);
            $('#give-complect-pickup-location').val(pickupLocationValue);
        }
        
        // Настройка скрытого календаря (только для передачи данных)
        $('#datepicker').datepicker({
            format: 'dd.mm.yyyy',
            language: 'ru',
            autoclose: true,
            todayHighlight: true
        });
        
        // Односторонняя синхронизация: с видимого календаря в скрытый
        $('#datepicker-preview').on('changeDate', function(e) {
            $('#datepicker').datepicker('setDate', $(this).datepicker('getDate'));
        });
        
        // Синхронизация при ручном вводе
        $('#datepicker-preview').on('change blur', function() {
            $('#datepicker').val($(this).val());
            // Обновляем скрытые поля для всех форм
            updateHiddenDateFields();
        });
        
        // Обновляем скрытые поля при изменении календаря
        $('#datepicker-preview').on('changeDate', function() {
            updateHiddenDateFields();
        });
        
        // Обновляем скрытое поле при изменении места выдачи
        $('#pickupLocation').on('change', function() {
            updateHiddenPickupLocation();
        });
        
        // Перед отправкой любой формы обновляем соответствующее скрытое поле
        $('form[action*="searchReader"]').on('submit', function() {
            var dateValue = $('#datepicker-preview').val();
            var pickupLocationValue = $('#pickupLocation').val();
            
            $('#reader-form-date').val(dateValue);
            $('#reader-form-pickup-location').val(pickupLocationValue);
            
            console.log('Отправляется форма поиска читателя с данными:', {
                date: dateValue,
                pickupLocation: pickupLocationValue
            });
        });
        
        $('form[action*="search"]:not([action*="searchReader"])').on('submit', function() {
            var dateValue = $('#datepicker-preview').val();
            var pickupLocationValue = $('#pickupLocation').val();
            
            $('#search-form-date').val(dateValue);
            $('#search-form-pickup-location').val(pickupLocationValue);
            
            console.log('Отправляется форма с данными:', {
                date: dateValue,
                pickupLocation: pickupLocationValue
            });
        });
        
        // Обработчик для формы выдачи комплекта
        $('form[action="/giveComplect"]').on('submit', function() {
            var dateValue = $('#datepicker-preview').val();
            var pickupLocationValue = $('#pickupLocation').val();
            
            // Обновляем все скрытые поля в форме выдачи комплекта
            $('#give-complect-pickup-location').val(pickupLocationValue);
            $(this).find('input[name="day"]').val(dateValue);
            
            console.log('Отправляется форма выдачи комплекта с данными:', {
                date: dateValue,
                pickupLocation: pickupLocationValue
            });
        });
    });
</script>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h3 class="mb-4 text-center">Введите данные</h3>
                
                <!-- Календарь для выбора даты возврата -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="datepicker-preview">Календарь (выберите дату возврата)</label>
                        <input type="text" class="form-control" id="datepicker-preview" placeholder="Выберите дату возврата" autocomplete="off" value="{{ session('returnDate') }}">
                </div>
                
                    
                    <div class="col-md-4">
                        <label for="pickupLocation">Место выдачи:</label>
                        <select id="pickupLocation" name="pickupLocation" class="form-select"> <!-- Bootstrap 5: form-select для выпадающих списков -->
                            <option value="lib1" {{ session('pickupLocation') == 'lib1' || !session('pickupLocation') ? 'selected' : '' }}>Библиотека 1</option>
                            <option value="lib2" {{ session('pickupLocation') == 'lib2' ? 'selected' : '' }}>Библиотека 2</option>
                            <option value="lib3" {{ session('pickupLocation') == 'lib3' ? 'selected' : '' }}>Библиотека 3</option>
                        </select>
                    </div>
                </div>

<!--               Форма поиска читателя                   -->
                <br>
                <p>Введите ID читателя</p>
                <form method="POST" action="{{ route('searchReader') }}">
                    @csrf
                    
                    <!-- Скрытое поле для передачи даты календаря -->
                    <input type="hidden" id="reader-form-date" name="calendar_date" value="">
                    
                    <!-- Скрытое поле для передачи места выдачи -->
                    <input type="hidden" id="reader-form-pickup-location" name="pickup_location" value="">
                    
                    <div class="mb-3">
                        <input 
                           
                          name="reader" 
                          class="form-control" 
                          placeholder="ID читателя" 
                          
                          required />
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Найти</button>
                    </div>
                </form>
                <br>
                <p>
                    
                @if(session('reader'))
                    читатель: {{ session('reader') }}
                @endif
                    
                </p>
                <br>
<!--               Форма поиска экземпляра                   -->
                <p>Введите инвентарный номер или штрихкод экземпляра из комплекта</p>
                <form method="POST" action="{{ route('search') }}">
                    @csrf
                    
                    <!-- Скрытое поле для передачи даты календаря -->
                    <input type="hidden" id="search-form-date" name="calendar_date" value="">
                    
                    <!-- Скрытое поле для передачи места выдачи -->
                    <input type="hidden" id="search-form-pickup-location" name="pickup_location" value="">
                    
                    <div class="mb-3">
                        <input 
                           
                          name="inputNumber" 
                          class="form-control" 
                          placeholder="Введите инвентарный номер или штрих-код" 
                          
                          required />
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Найти</button>
                    </div>
                </form>
                                       
                        <?php
                        if(isset($result["records"][0])){

                            echo '<div class="alert alert-success mt-4" role="alert">';
                            
                            //dd($result["records"]);
                            foreach ($result["records"] as $item) {
                                echo "<h5>экземпляр:</h5>".$item[1]."<br>";
                                echo "<h5>инвентарный номер:</h5>".$invNumFromDB."<br>";
                                if(isset($barcode) && $barcode != "штрихкод не найден"){
                                    echo "<h5>штрих-код:</h5>".$barcode."<br>";
                                }
                                echo "<h5>что вы искали:</h5>".$invNum."<br>";
                                if(isset($bookStatus)){
                                    
                                    if($bookStatus=="Утерян" || $bookStatus=="Списан"){
                                        
                                        echo "<div style='color:red;'><h5>статус:</h5>".$bookStatus."</div><br>";    
                                    }else{
                                        
                                        echo "<h5>статус:</h5>".$bookStatus."<br>";
                                    }
                                }else{
                                    echo "<h5>не удалось получить статус</h5>";
                                }
                            
                            echo "<br><hr><br>";
                            }
                        echo '</div>';
                        }
                        ?>    
                        
                    

                    <div>
                                         
                        <?php
                        if(isset($complectRecs)){
                            if(count($complectRecs) > 0){                            
                                $number = 1;
                                echo "<h2>Записи в комплекте</h2>";
                                echo "<table border='1'>";
                                echo "<tr><th>№</th><th>Экземпляр</th><th>Инв. номер</th></tr>";
                                foreach ($complectRecs as $rec) {
                                    $rec = explode("<br>", $rec);
                                    echo "<tr border='1'>";
                                    echo "<td>".$number."</td><td>".$rec[0]."</td><td>".$rec[1]."</td>";
                                    echo "</tr>";
                                    $number++;
                                }
                                echo "</table>";
                            }
                        
                        }
                        ?>  
                        @if(isset($complectRecs))
                        @if($complectStatus && $complectStatus != 1)<!-- если $complectStatus==1 значит книга не состоит в комплекте-->
                        <h4>Комплект доступен для выдачи</h4>
                        @endif
                        @if($complectStatus == false && $complectStatus != 1)
                        <h4>Комплект выдан читателю</h4>
                        @endif
                        <div class="container mt-5">
                            <form action="/giveComplect" method="post">
                            @csrf
                                @if(Auth::check() && Auth::user()->name && session('reader') && count($complectRecs) > 1 && $complectStatus && $bookStatus!="Выдан читателю")
                                <input type="hidden" class="form-control" name="librarian" value="{{ Auth::user()->name }}">
                                
                                <input type="hidden" class="form-control" name="reader" value="{{ session('reader') }}">
                                
                                <!-- Скрытое поле для передачи места выдачи -->
                                <input type="hidden" id="give-complect-pickup-location" name="pickup_location" value="">

                                <?php
                                if(isset($complectRecs)){
                                    if(count($complectRecs) > 0){                            
                                        $bookNum = 1;
                                        
                                        foreach ($complectRecs as $rec) {
                                            echo "<input type='hidden' name='book". $bookNum . "' value='" . $rec . "'>";
                                            $bookNum++;
                                        }
                                    echo "<input type='hidden' name='booksAmount' value='".$bookNum."'>";
                                    }
                                }
                                ?>  

                                <div class="form-group" style="display: none;">
                                    <label for="datepicker">Календарь (дата возврата для выдачи)</label>
                                    <input type="text" class="form-control" id="datepicker" name="day" placeholder="Выберите дату возврата" autocomplete="off" value="{{ session('returnDate') }}">
                                </div>
                                <br>
                                
                                <button type="submit" class="btn btn-primary" name="send">Выдать комплект</button>
                                
                                @endif
                            </form>
                        </div>
                        @endif
                    </div>
                
            </div>
        </div>
    </div>
</div>


@endsection
