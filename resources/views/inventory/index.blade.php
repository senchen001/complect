@extends('layouts.app')

@section('content')

<style>
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
        <div class="accordion" id="accordionExample">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                  Настройки
                </button>
              </h2>
              <div id="collapseForm" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <div class="row">
                    <div class="col-md-3">
                      <label for="db">База данных:</label>
                    </div>
                    <div class="col-md-3">
                      <select id="db" name="db">
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
                </div>
              </div>
            </div>
          </div>
          
        </div>
        <br>
        <div class="form-group">
            <label for="invNum">Инвентаный номер или ш-к экземпляра</label>
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="invNum" id="invNum" required>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-success">Найти</button>
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
        <br>
        <div class="form-group">
            <label for="booksNum">Количество экземпляров</label>
            <input type="text" class="form-control" name="booksNum"  value="1" required>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Найти</button>
    </form>
</div>

@if(isset($invStatus))
<div class="container mt-4">
    <h2>Результат проверки</h2>
    <form action="{{ route('approveAccepted') }}" method="POST">
        @csrf
        
        <button type="submit" class="btn btn-success">Инвентаризировать</button>
        
        <div class="row">
            
            <input type="hidden" class="form-control" name="librarian" value="{{ Auth::user()->name }}">
        </div>
        <hr>
        <br>
         
         <div class="row">
            <div class="col-md-3">
                <img src="img/{{ $cover }}" alt="Обложка" style="max-width: 100px; height: auto;">
            </div>
            <div class="col-md-3">
                {{ $bookDescr }}
            </div>
            <input type="hidden" class="form-control" name="bookDescr" value="{{ $bookDescr }}">
        </div>
        <hr>     
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
                <label for="rastShifr">Штрих-код/RFID:</label>
            </div>
            <div class="col-md-3">
                {{ $barcode }}
            </div>
            <input type="hidden" class="form-control" name="barcode" value="{{ $barcode }}">
        </div>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <label for="rastShifr">Статус экземпляра:</label>
            </div>
            
            <div class="col-md-3">
                {{ $bookStatus['status'] }} - {{ $bookStatus['statusDescr'] }}
            </div>
            
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
                    {{ $storLocFound['storLoc'] }}<br>
                    {{ $storLocFound['storLocDescr'] }}
                @else
                    <p class="text-danger"> {{ $storLocFound['storLoc'] }} <br> {{ $storLocFound['storLocDescr'] }}</p>
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

<!-- Подключение библиотеки QuaggaJS для сканирования штрихкодов -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

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

    // Код сканера штрихкодов
    const invNumField = document.getElementById('invNum');
    if (!invNumField) {
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
                    invNumField.value = code;
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
                        onclick="document.getElementById('invNum').value='${code}'; this.closest('.alert').style.background='#d4edda';">
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