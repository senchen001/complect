@extends('layouts.app')

@section('content')
<script src="js/jquery-3.5.1.slim.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/bootstrap-datepicker.ru.min.js"></script>

<style>

/*****************************************/
    /* Стили для выделения текущей даты */
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

    /* Улучшенные стили для мобильного сканера штрихкодов 1080p */
    #search-scanner-container {
        position: relative;
        width: 100% !important;
        max-width: 100% !important;
        height: 400px !important;
        min-height: 350px;
        overflow: hidden;
        border: 3px solid #007bff;
        border-radius: 12px;
        background: #000;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    #searchInteractive {
        width: 100% !important;
        height: 100% !important;
        position: relative;
    }

    #searchInteractive video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
    
    }

    #searchInteractive canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: 100% !important;
        
    }

    /* Стиль для рамки сканирования высокого разрешения */
    #search-scanner-container::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 85%;
        height: 120px;
        border: 3px solid #ff0040;
        border-radius: 12px;
        box-shadow: 
            0 0 0 3px rgba(255, 0, 64, 0.4),
            inset 0 0 0 3px rgba(255, 255, 255, 0.9),
            0 0 20px rgba(255, 0, 64, 0.6);
        pointer-events: none;
        z-index: 10;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 
                0 0 0 3px rgba(255, 0, 64, 0.4),
                inset 0 0 0 3px rgba(255, 255, 255, 0.9),
                0 0 20px rgba(255, 0, 64, 0.6);
        }
        50% {
            box-shadow: 
                0 0 0 3px rgba(255, 0, 64, 0.7),
                inset 0 0 0 3px rgba(255, 255, 255, 1),
                0 0 25px rgba(255, 0, 64, 0.8);
        }
        100% {
            box-shadow: 
                0 0 0 3px rgba(255, 0, 64, 0.4),
                inset 0 0 0 3px rgba(255, 255, 255, 0.9),
                0 0 20px rgba(255, 0, 64, 0.6);
        }
    }

    /* Инструкции для сканера высокого разрешения */
    #search-scanner-container::before {
        content: '📱 Поместите штрихкод в красную рамку!';
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        background: rgba(0, 0, 0, 0.8);
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: bold;
        z-index: 10;
        pointer-events: none;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Адаптивность для мобильных устройств с высоким разрешением */
    @media (max-width: 768px) {
        #searchScannerSection .col-md-6:first-child {
            width: 100%;
            margin-bottom: 20px;
        }
        
        #search-scanner-container {
            height: 350px !important;
            border-width: 2px;
        }
        
        #search-scanner-container::after {
            height: 100px;
            border-width: 2px;
        }
        
        #search-scanner-container::before {
            font-size: 13px;
            padding: 6px 10px;
        }
        
        #searchScannerSection .col-md-6:last-child {
            width: 100%;
        }
        
        #searchScannerSection .row {
            margin: 0;
        }
        
        #searchScannerSection ul {
            font-size: 0.85em;
            padding-left: 15px;
        }
        
        #searchDetectedCodes .alert {
            font-size: 0.85em;
            padding: 8px;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 480px) {
        #search-scanner-container {
            height: 300px !important;
        }
        
        #search-scanner-container::after {
            height: 80px;
            width: 90%;
        }
        
        #search-scanner-container::before {
            font-size: 12px;
            padding: 5px 8px;
        }
    }

    /* Дополнительные стили для улучшения UX */
    .btn-group-scanner {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 15px;
    }

    .btn-group-scanner .btn {
        flex: 1;
        max-width: 150px;
    }

    #searchScannerStatus {
        text-align: center;
        font-weight: 500;
        padding: 5px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.1);
    }

    #searchDetectedCodes {
        max-height: 300px;
        overflow-y: auto;
    }

    #searchDetectedCodes::-webkit-scrollbar {
        width: 4px;
    }

    #searchDetectedCodes::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    #searchDetectedCodes::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 2px;
    }

    #searchDetectedCodes::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
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
            $('#pickupLocation').val('ucho');
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
                <h3 class="mb-4 text-center">Заполните данные</h3>
                
                <!-- Календарь для выбора даты возврата -->
                <div class="row">
                    <div class="col-md-6">
                        <label for="datepicker-preview">Календарь (выберите дату возврата)</label>
                        <input type="text" class="form-control" id="datepicker-preview" placeholder="Выберите дату возврата" autocomplete="off" value="{{ session('returnDate') }}">
                    </div>
                
                    <div class="col-md-4">
                        <label for="pickupLocation">Место выдачи:</label>
                        <select id="pickupLocation" name="pickupLocation" class="form-select"> <!-- Bootstrap 5: form-select для выпадающих списков -->
                            <option value="ucho" {{ session('pickupLocation') == 'ucho' || !session('pickupLocation') ? 'selected' : '' }}>учо</option>
                            <option value="ab1" {{ session('pickupLocation') == 'ab1' ? 'selected' : '' }}>аб1</option>
                            <option value="ab2" {{ session('pickupLocation') == 'ab2' ? 'selected' : '' }}>аб2</option>
                            <option value="ab3" {{ session('pickupLocation') == 'ab3' ? 'selected' : '' }}>аб3</option>
                            <option value="chz1" {{ session('pickupLocation') == 'chz1' ? 'selected' : '' }}>чз1</option>
                            <option value="chz2" {{ session('pickupLocation') == 'chz2' ? 'selected' : '' }}>чз2</option>
                            <option value="chz3" {{ session('pickupLocation') == 'chz3' ? 'selected' : '' }}>чз3</option>
                        </select>
                    </div>
                </div>

<!--               Форма поиска читателя                   -->
                <br>
                <p>введите ID читателя</p>
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
                    читателя: {{ session('reader') }}
                @endif
                    
                </p>
                <br>
<!--               Форма поиска экземпляра                   -->
                <p>введите инвентарный номер или штрихкод экземпляра из комплекта</p>
                <form method="POST" action="{{ route('search') }}">
                    @csrf
                    
                    <!-- Скрытое поле для передачи даты календаря -->
                    <input type="hidden" id="search-form-date" name="calendar_date" value="">
                    
                    <!-- Скрытое поле для передачи места выдачи -->
                    <input type="hidden" id="search-form-pickup-location" name="pickup_location" value="">
                    
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <input 
                                    name="inputNumber" 
                                    id="inputNumber"
                                    class="form-control" 
                                    placeholder="введите инвентарный номер или штрих-код" 
                                    required />
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-info" id="searchScanButton">
                                    📷 Сканировать
                                </button>
                            </div>
                        </div>
                        
                        <!-- Сканер штрихкодов для поиска -->
                        <div id="searchScannerSection" style="display: none; margin-top: 15px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div id="search-scanner-container">
                                        <div id="searchInteractive" class="viewport"></div>
                                    </div>
                                    <div class="btn-group-scanner">
                                        <button type="button" class="btn btn-success btn-sm" id="searchStartScan">Начать сканирование</button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="searchCloseScan">Закрыть</button>
                                    </div>
                                    <div id="searchScannerStatus" class="mt-2" style="font-size: 0.9em;"></div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Инструкции по сканированию:</h6>
                                    <ul style="font-size: 0.9em;">
                                        <li>Наведите камеру на штрихкод</li>
                                        <li>Убедитесь, что штрихкод находится в красной рамке</li>
                                        <li>Держите устройство неподвижно</li>
                                        <li>Обеспечьте хорошее освещение</li>
                                        <li>Расстояние: 15-30 см до штрихкода</li>
                                    </ul>
                                    <div id="searchDetectedCodes" style="margin-top: 15px;">
                                        <h6>Распознанные коды:</h6>
                                        <div id="searchCodesList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Найти</button>
                    </div>
                </form>
                                       
                        <?php
                        if(isset($result["records"][0])){

                            echo '<div class="alert alert-success mt-4" role="alert">';

                            foreach ($result["records"] as $item) {
                            // Внешний flex-контейнер для одной записи
    echo '<div style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 30px;">';

    // Левая колонка — описание экземпляра
    echo '<div style="flex: 1;">';
    echo "<h5>экземпляр:</h5>" . $item[1] . "<br>";
    echo "<h5>инвентарный номер:</h5>" . $invNumFromDB . "<br>";
    if (isset($barcode) && $barcode != "штрихкод не найден") {
        echo "<h5>штрих-код:</h5>" . $barcode . "<br>";
    }
    echo "<h5>что вы искали:</h5>" . $invNum . "<br>";
    if (isset($bookStatus)) {
        if ($bookStatus == "Утерян" || $bookStatus == "Списан") {
            echo "<div style='color:red;'><h5>статус:</h5>" . $bookStatus . "</div><br>";
        } else {
            echo "<h5>статус:</h5>" . $bookStatus . "<br>";
        }
    } else {
        echo "<h5>не удалось получить статус</h5>";
    }
    echo '</div>';

    // Правая колонка — изображение
    echo '<div style="flex-shrink: 0;">';
        if($cover != "no"){
            
            echo '<img src="data:image/jpeg;base64,'. $cover .'" alt="IRBIS Image" style="max-width: 150px; height: auto;">';
        }else{
            echo '<img src="img/defaultCover.jpg" alt="IRBIS Image" style="max-width: 150px; height: auto;">';
        }        
    echo '</div>';

    echo '</div>'; // конец flex-контейнера для записи
}

echo '</div>'; // конец alert
                        }
                        ?>    
                        
                    

                    <div>
                                         
                        <?php
                        if(isset($complectRecs)){
                            if(count($complectRecs) > 0){                            
                                $number = 1;
                                echo "<h2>Записи в комплекте</h2>";
                                echo "<table border='1'>";
                                echo "<tr><th>№</th><th>Экземпляр</th><th>Инв. номер</th><th>Обложка</th></tr>";
                                foreach ($complectRecs as $rec) {
                                
                                    $rec = explode("<br>", $rec);
                                    echo "<tr border='1'>";
                                    echo "<td>".$number."</td><td>".$rec[0]."</td><td>".$rec[1]."</td>";
                                    $cover2 = ltrim($rec[2]);
                                    echo "<td>";
                                        if($cover2 != "no"){
            
            echo '<img src="data:image/jpeg;base64,'. $cover2 .'" alt="IRBIS Image" style="max-width: 100px; height: auto;">';
        }else{
            echo '<img src="img/defaultCover.jpg" alt="IRBIS Image" style="max-width: 150px; height: auto;">';
        } 
                                    echo "</td>";
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
                                @if(Auth::check() && Auth::user()->name && session('reader') && count($complectRecs) > 1 && $complectStatus && $bookStatus!="выдан читателю" && null !== session("returnDate"))
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
                                
                                <button type="submit" class="btn btn-primary" name="send">выдать комплект</button>
                                
                                @endif
                            </form>
                        </div>
                        @endif
                    </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Подключение библиотеки QuaggaJS для сканирования штрихкодов -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли на странице поле для поиска
    const inputNumberField = document.getElementById('inputNumber');
    if (!inputNumberField) {
        return; // Выходим, если поле не найдено
    }

    let searchScannerStarted = false;
    let searchDetectedCodes = new Set(); // Для избежания дублирующих кодов
    
    const searchScanButton = document.getElementById('searchScanButton');
    const searchScannerSection = document.getElementById('searchScannerSection');
    const searchStartScanBtn = document.getElementById('searchStartScan');
    const searchCloseScanBtn = document.getElementById('searchCloseScan');
    const searchScannerStatus = document.getElementById('searchScannerStatus');
    const searchCodesList = document.getElementById('searchCodesList');
    
    // Показать/скрыть секцию сканера
    searchScanButton.addEventListener('click', function() {
        if (searchScannerSection.style.display === 'none') {
            searchScannerSection.style.display = 'block';
            searchScanButton.innerHTML = '📷 Скрыть сканер';
        } else {
            stopSearchScanner();
            searchScannerSection.style.display = 'none';
            searchScanButton.innerHTML = '📷 Сканировать';
        }
    });
    
    // Начать сканирование
    searchStartScanBtn.addEventListener('click', function() {
        startSearchScanner();
    });
    
    // Закрыть сканер
    searchCloseScanBtn.addEventListener('click', function() {
        stopSearchScanner();
        searchScannerSection.style.display = 'none';
        searchScanButton.innerHTML = '📷 Сканировать';
    });
    
    function startSearchScanner() {
        if (searchScannerStarted) {
            return;
        }
        
        updateSearchStatus('Инициализация камеры...', 'info');
        
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#searchInteractive'),
                constraints: {
                    width: { min: 640, ideal: 1920, max: 1920 },
                    height: { min: 480, ideal: 1080, max: 1080 },
                    facingMode: "environment", // Задняя камера
                    focusMode: "continuous", // Непрерывный автофокус
                    advanced: [
                        { focusMode: "continuous" },
                        { focusDistance: { min: 0.1, max: Infinity } },
                        { zoom: { min: 1, max: 3 } },
                        { torch: false }
                    ]
                }
            },
            locator: {
                patchSize: "large", // Увеличиваем размер патча для лучшего распознавания
                halfSample: false, // Отключаем половинную выборку для лучшего качества
                showCanvas: true,
                showPatches: false,
                showFoundPatches: false,
                showSkeleton: false,
                showLabels: false,
                showPatchLabels: false,
                showBoundingBox: true
            },
            numOfWorkers: navigator.hardwareConcurrency || 4,
            frequency: 20, // Увеличиваем частоту сканирования для высокого разрешения
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader", 
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "codabar_reader",
                    "i2of5_reader"
                ],
                debug: {
                    showCanvas: false,
                    showPatches: false,
                    showFoundPatches: false,
                    showSkeleton: false,
                    showLabels: false,
                    showPatchLabels: false,
                    showBoundingBox: false,
                    showRemainingPatchLabels: false
                },
                multiple: false
            },
            locate: true,
            area: { // Определяем область сканирования
                top: "15%",
                right: "5%", 
                left: "5%",
                bottom: "15%"
            }
        }, function(err) {
            if (err) {
                console.log(err);
                updateSearchStatus('Ошибка инициализации камеры: ' + err.message, 'error');
                
                // Попытка с пониженным разрешением при ошибке
                if (err.name === 'NotSupportedError' || err.name === 'OverconstrainedError') {
                    updateSearchStatus('Попытка инициализации с пониженным разрешением...', 'info');
                    startSearchScannerFallback();
                }
                return;
            }
            console.log("Initialization finished. Ready to start");
            
            // Настраиваем размер канваса для мобильного устройства
            const canvas = document.querySelector('#searchInteractive canvas');
            const video = document.querySelector('#searchInteractive video');
            
            if (canvas && video) {
                // Делаем видео адаптивным
                video.style.width = '100%';
                video.style.height = 'auto';
                video.style.maxHeight = '400px';
                video.style.objectFit = 'cover';
                
                // Настраиваем canvas
                canvas.style.width = '100%';
                canvas.style.height = 'auto';
                canvas.style.maxHeight = '400px';
            }
            
            Quagga.start();
            searchScannerStarted = true;
            updateSearchStatus('Сканирование активно в высоком разрешении. Держите штрихкод в рамке.', 'success');
            
            searchStartScanBtn.disabled = true;
            searchStartScanBtn.innerHTML = 'Сканирование активно...';
            searchStartScanBtn.className = 'btn btn-warning btn-sm';
        });
        
        // Улучшенная обработка распознанных кодов
        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            
            // Более строгая проверка качества для высокого разрешения
            if (result.codeResult.decodedCodes && result.codeResult.decodedCodes.length > 0) {
                // Вычисляем среднюю ошибку
                const errors = result.codeResult.decodedCodes
                    .map(decoded => decoded.error || 0)
                    .filter(error => error !== undefined);
                
                const avgError = errors.length > 0 ? 
                    errors.reduce((sum, error) => sum + error, 0) / errors.length : 0;
                
                // Проверяем длину кода (большинство штрихкодов имеют определенную длину)
                const isValidLength = code.length >= 6 && code.length <= 25;
                
                // Проверяем, что код состоит только из цифр или допустимых символов
                const isValidFormat = /^[0-9A-Za-z\-\.\_\+\*\$\/\%\@\#\!]+$/.test(code);
                
                // Для высокого разрешения снижаем порог ошибки
                const errorThreshold = 0.08;
                
                // Принимаем код только если качество хорошее и код валидный
                if (avgError < errorThreshold && isValidLength && isValidFormat && !searchDetectedCodes.has(code)) {
                    searchDetectedCodes.add(code);
                    inputNumberField.value = code;
                    updateSearchStatus('Штрихкод распознан: ' + code, 'success');
                    addToSearchDetectedList(code);
                    
                    // Небольшая задержка перед следующим сканированием
                    setTimeout(() => {
                        updateSearchStatus('Готов к следующему сканированию', 'info');
                    }, 2000);
                }
            }
        });
        
        // Обработка ошибок процесса
        Quagga.onProcessed(function(result) {
            const drawingCtx = Quagga.canvas.ctx.overlay;
            const drawingCanvas = Quagga.canvas.dom.overlay;
            
            if (result) {
                // Очищаем предыдущие отрисовки
                drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), 
                                   parseInt(drawingCanvas.getAttribute("height")));
                
                if (result.boxes) {
                    drawingCtx.strokeStyle = "#00ff00";
                    drawingCtx.lineWidth = 2;
                    
                    result.boxes.filter(function (box) {
                        return box !== result.box;
                    }).forEach(function (box) {
                        Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "#00ff00", lineWidth: 2});
                    });
                }
                
                if (result.box) {
                    drawingCtx.strokeStyle = "#0080ff";
                    drawingCtx.lineWidth = 3;
                    Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#0080ff", lineWidth: 3});
                }
                
                if (result.codeResult && result.codeResult.code) {
                    drawingCtx.strokeStyle = "#ff0000";
                    drawingCtx.lineWidth = 4;
                    Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: '#ff0000', lineWidth: 4});
                }
            }
        });
    }
    
    // Fallback функция с пониженным разрешением
    function startSearchScannerFallback() {
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#searchInteractive'),
                constraints: {
                    width: { min: 480, ideal: 720, max: 1280 },
                    height: { min: 320, ideal: 480, max: 720 },
                    facingMode: "environment",
                    focusMode: "continuous"
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: false,
                showCanvas: true,
                showBoundingBox: true
            },
            numOfWorkers: navigator.hardwareConcurrency || 2,
            frequency: 15,
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader", 
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader"
                ],
                multiple: false
            },
            locate: true,
            area: {
                top: "20%",
                right: "10%", 
                left: "10%",
                bottom: "20%"
            }
        }, function(err) {
            if (err) {
                console.log(err);
                updateSearchStatus('Не удалось инициализировать камеру: ' + err.message, 'error');
                return;
            }
            
            const canvas = document.querySelector('#searchInteractive canvas');
            const video = document.querySelector('#searchInteractive video');
            
            if (canvas && video) {
                video.style.width = '100%';
                video.style.height = 'auto';
                video.style.maxHeight = '400px';
                video.style.objectFit = 'cover';
                
                canvas.style.width = '100%';
                canvas.style.height = 'auto';
                canvas.style.maxHeight = '400px';
            }
            
            Quagga.start();
            searchScannerStarted = true;
            updateSearchStatus('Сканирование активно (стандартное разрешение). Держите штрихкод в рамке.', 'success');
            
            searchStartScanBtn.disabled = true;
            searchStartScanBtn.innerHTML = 'Сканирование активно...';
            searchStartScanBtn.className = 'btn btn-warning btn-sm';
        });
    }
    
    function stopSearchScanner() {
        if (searchScannerStarted) {
            Quagga.stop();
            searchScannerStarted = false;
            searchDetectedCodes.clear(); // Очищаем набор найденных кодов при остановке
            updateSearchStatus('Сканирование остановлено', 'info');
            
            searchStartScanBtn.disabled = false;
            searchStartScanBtn.innerHTML = 'Начать сканирование';
            searchStartScanBtn.className = 'btn btn-success btn-sm';
        }
    }
    
    function updateSearchStatus(message, type) {
        searchScannerStatus.textContent = message;
        searchScannerStatus.className = 'mt-2';
        
        if (type === 'success') {
            searchScannerStatus.style.color = '#28a745';
            searchScannerStatus.style.fontWeight = 'bold';
        } else if (type === 'error') {
            searchScannerStatus.style.color = '#dc3545';
            searchScannerStatus.style.fontWeight = 'bold';
        } else {
            searchScannerStatus.style.color = '#17a2b8';
            searchScannerStatus.style.fontWeight = 'normal';
        }
    }
    
    function addToSearchDetectedList(code) {
        const codeElement = document.createElement('div');
        codeElement.className = 'alert alert-success py-1 px-2 mb-1';
        codeElement.style.fontSize = '0.9em';
        codeElement.style.borderLeft = '4px solid #28a745';
        codeElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><strong>📋 ${code}</strong></span>
                <button type="button" class="btn btn-sm btn-primary py-0 px-2" 
                        onclick="document.getElementById('inputNumber').value='${code}'; this.closest('.alert').style.background='#d4edda';">
                    Использовать
                </button>
            </div>
        `;
        
        // Добавляем в начало списка
        if (searchCodesList.firstChild) {
            searchCodesList.insertBefore(codeElement, searchCodesList.firstChild);
        } else {
            searchCodesList.appendChild(codeElement);
        }
        
        // Ограничиваем количество сохраненных кодов
        while (searchCodesList.children.length > 8) {
            searchCodesList.removeChild(searchCodesList.lastChild);
        }
    }
    
    // Очистка при закрытии страницы
    window.addEventListener('beforeunload', function() {
        if (searchScannerStarted) {
            Quagga.stop();
        }
    });
    
    // Обработка видимости страницы для экономии ресурсов
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && searchScannerStarted) {
            // Приостанавливаем сканирование когда страница не видна
            Quagga.pause();
            updateSearchStatus('Сканирование приостановлено', 'info');
        } else if (!document.hidden && searchScannerStarted) {
            // Возобновляем сканирование когда страница снова видна
            Quagga.start();
            updateSearchStatus('Сканирование возобновлено', 'success');
        }
    });
});
</script>

@endsection