@extends('layouts.app')

@section('content')

<!-- Подключение библиотек для сканера -->
<script src="js/jquery-3.5.1.slim.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>

<!-- Стили для сканера штрихкодов -->
<style>
    /* Улучшенные стили для мобильного сканера штрихкодов 1080p */
    #complect-scanner-container {
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

    #complectInteractive {
        width: 100% !important;
        height: 100% !important;
        position: relative;
    }

    #complectInteractive video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
    }

    #complectInteractive canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: 100% !important;
    }

    /* Стиль для рамки сканирования высокого разрешения */
    #complect-scanner-container::after {
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
    #complect-scanner-container::before {
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
        #complectScannerSection .col-md-6:first-child {
            width: 100%;
            margin-bottom: 20px;
        }
        
        #complect-scanner-container {
            height: 350px !important;
            border-width: 2px;
        }
        
        #complect-scanner-container::after {
            height: 100px;
            border-width: 2px;
        }
        
        #complect-scanner-container::before {
            font-size: 13px;
            padding: 6px 10px;
        }
        
        #complectScannerSection .col-md-6:last-child {
            width: 100%;
        }
        
        #complectScannerSection .row {
            margin: 0;
        }
        
        #complectScannerSection ul {
            font-size: 0.85em;
            padding-left: 15px;
        }
        
        #complectDetectedCodes .alert {
            font-size: 0.85em;
            padding: 8px;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 480px) {
        #complect-scanner-container {
            height: 300px !important;
        }
        
        #complect-scanner-container::after {
            height: 80px;
            width: 90%;
        }
        
        #complect-scanner-container::before {
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

    #complectScannerStatus {
        text-align: center;
        font-weight: 500;
        padding: 5px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.1);
    }

    #complectDetectedCodes {
        max-height: 300px;
        overflow-y: auto;
    }

    #complectDetectedCodes::-webkit-scrollbar {
        width: 4px;
    }

    #complectDetectedCodes::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    #complectDetectedCodes::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 2px;
    }

    #complectDetectedCodes::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
    }

    /* Базовые стили */
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
                <label for="invnum">Инвентарный номер или штрих-код/RFID экземпляра</label>
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="invnum" id="invnumInput" required>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-info" id="complectScanButton">
                            📷 Сканировать
                        </button>
                    </div>
                </div>
                
                <!-- Сканер штрихкодов для комплекта -->
                <div id="complectScannerSection" style="display: none; margin-top: 15px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="complect-scanner-container">
                                <div id="complectInteractive" class="viewport"></div>
                            </div>
                            <div class="btn-group-scanner">
                                <button type="button" class="btn btn-success btn-sm" id="complectStartScan">Начать сканирование</button>
                                <button type="button" class="btn btn-secondary btn-sm" id="complectCloseScan">Закрыть</button>
                            </div>
                            <div id="complectScannerStatus" class="mt-2" style="font-size: 0.9em;"></div>
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
                            <div id="complectDetectedCodes" style="margin-top: 15px;">
                                <h6>Распознанные коды:</h6>
                                <div id="complectCodesList"></div>
                            </div>
                        </div>
                    </div>
                </div>
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

<!-- Подключение библиотеки QuaggaJS для сканирования штрихкодов -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли на странице поле для ввода инвентарного номера
    const invnumInput = document.getElementById('invnumInput');
    if (!invnumInput) {
        return; // Выходим, если поле не найдено
    }

    let complectScannerStarted = false;
    let complectDetectedCodes = new Set(); // Для избежания дублирующихся кодов
    
    const complectScanButton = document.getElementById('complectScanButton');
    const complectScannerSection = document.getElementById('complectScannerSection');
    const complectStartScanBtn = document.getElementById('complectStartScan');
    const complectCloseScanBtn = document.getElementById('complectCloseScan');
    const complectScannerStatus = document.getElementById('complectScannerStatus');
    const complectCodesList = document.getElementById('complectCodesList');
    
    // Показать/скрыть секцию сканера
    complectScanButton.addEventListener('click', function() {
        if (complectScannerSection.style.display === 'none') {
            complectScannerSection.style.display = 'block';
            complectScanButton.innerHTML = '📷 Скрыть сканер';
        } else {
            stopComplectScanner();
            complectScannerSection.style.display = 'none';
            complectScanButton.innerHTML = '📷 Сканировать';
        }
    });
    
    // Начать сканирование
    complectStartScanBtn.addEventListener('click', function() {
        startComplectScanner();
    });
    
    // Закрыть сканер
    complectCloseScanBtn.addEventListener('click', function() {
        stopComplectScanner();
        complectScannerSection.style.display = 'none';
        complectScanButton.innerHTML = '📷 Сканировать';
    });
    
    function startComplectScanner() {
        if (complectScannerStarted) {
            return;
        }
        
        updateComplectStatus('Инициализация камеры...', 'info');
        
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#complectInteractive'),
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
                updateComplectStatus('Ошибка инициализации камеры: ' + err.message, 'error');
                
                // Попытка с пониженным разрешением при ошибке
                if (err.name === 'NotSupportedError' || err.name === 'OverconstrainedError') {
                    updateComplectStatus('Попытка инициализации с пониженным разрешением...', 'info');
                    startComplectScannerFallback();
                }
                return;
            }
            console.log("Initialization finished. Ready to start");
            
            // Настраиваем размер канваса для мобильного устройства
            const canvas = document.querySelector('#complectInteractive canvas');
            const video = document.querySelector('#complectInteractive video');
            
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
            complectScannerStarted = true;
            updateComplectStatus('Сканирование активно в высоком разрешении. Держите штрихкод в рамке.', 'success');
            
            complectStartScanBtn.disabled = true;
            complectStartScanBtn.innerHTML = 'Сканирование активно...';
            complectStartScanBtn.className = 'btn btn-warning btn-sm';
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
                if (avgError < errorThreshold && isValidLength && isValidFormat && !complectDetectedCodes.has(code)) {
                    complectDetectedCodes.add(code);
                    invnumInput.value = code;
                    updateComplectStatus('Штрихкод распознан: ' + code, 'success');
                    addToComplectDetectedList(code);
                    
                    // Небольшая задержка перед следующим сканированием
                    setTimeout(() => {
                        updateComplectStatus('Готов к следующему сканированию', 'info');
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
    function startComplectScannerFallback() {
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#complectInteractive'),
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
                updateComplectStatus('Не удалось инициализировать камеру: ' + err.message, 'error');
                return;
            }
            
            const canvas = document.querySelector('#complectInteractive canvas');
            const video = document.querySelector('#complectInteractive video');
            
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
            complectScannerStarted = true;
            updateComplectStatus('Сканирование активно (стандартное разрешение). Держите штрихкод в рамке.', 'success');
            
            complectStartScanBtn.disabled = true;
            complectStartScanBtn.innerHTML = 'Сканирование активно...';
            complectStartScanBtn.className = 'btn btn-warning btn-sm';
        });
    }
    
    function stopComplectScanner() {
        if (complectScannerStarted) {
            Quagga.stop();
            complectScannerStarted = false;
            complectDetectedCodes.clear(); // Очищаем набор найденных кодов при остановке
            updateComplectStatus('Сканирование остановлено', 'info');
            
            complectStartScanBtn.disabled = false;
            complectStartScanBtn.innerHTML = 'Начать сканирование';
            complectStartScanBtn.className = 'btn btn-success btn-sm';
        }
    }
    
    function updateComplectStatus(message, type) {
        complectScannerStatus.textContent = message;
        complectScannerStatus.className = 'mt-2';
        
        if (type === 'success') {
            complectScannerStatus.style.color = '#28a745';
            complectScannerStatus.style.fontWeight = 'bold';
        } else if (type === 'error') {
            complectScannerStatus.style.color = '#dc3545';
            complectScannerStatus.style.fontWeight = 'bold';
        } else {
            complectScannerStatus.style.color = '#17a2b8';
            complectScannerStatus.style.fontWeight = 'normal';
        }
    }
    
    function addToComplectDetectedList(code) {
        const codeElement = document.createElement('div');
        codeElement.className = 'alert alert-success py-1 px-2 mb-1';
        codeElement.style.fontSize = '0.9em';
        codeElement.style.borderLeft = '4px solid #28a745';
        codeElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><strong>📋 ${code}</strong></span>
                <button type="button" class="btn btn-sm btn-primary py-0 px-2" 
                        onclick="document.getElementById('invnumInput').value='${code}'; this.closest('.alert').style.background='#d4edda';">
                    Использовать
                </button>
            </div>
        `;
        
        // Добавляем в начало списка
        if (complectCodesList.firstChild) {
            complectCodesList.insertBefore(codeElement, complectCodesList.firstChild);
        } else {
            complectCodesList.appendChild(codeElement);
        }
        
        // Ограничиваем количество сохраненных кодов
        while (complectCodesList.children.length > 8) {
            complectCodesList.removeChild(complectCodesList.lastChild);
        }
    }
    
    // Очистка при закрытии страницы
    window.addEventListener('beforeunload', function() {
        if (complectScannerStarted) {
            Quagga.stop();
        }
    });
    
    // Обработка видимости страницы для экономии ресурсов
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && complectScannerStarted) {
            // Приостанавливаем сканирование когда страница не видна
            Quagga.pause();
            updateComplectStatus('Сканирование приостановлено', 'info');
        } else if (!document.hidden && complectScannerStarted) {
            // Возобновляем сканирование когда страница снова видна
            Quagga.start();
            updateComplectStatus('Сканирование возобновлено', 'success');
        }
    });
});
</script>

@endsection