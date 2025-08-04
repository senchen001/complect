<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\borrowedBook;
use Exception;

require_once app_path('Http/Controllers/irbis_class.php');
class makeComplectController extends Controller
{
    public $irbisServerPort;
    public $DB_RDRKV;

    public function __construct(){
        $this->irbisServerPort = config('app.irbisServerPort');
        $this->DB_RDRKV = config('app.complectDataBase');
    }

    public function Show(){
        $makingComplect = Session::get('makingComplect', false);
        $newComplectNumber = Session::get('newComplectNumber', null);
        $thisComplect = [];
        
        // Если комплект создается, загружаем его текущий состав
        if ($makingComplect && $newComplectNumber) {
            
            $irbis = new \irbis64('127.0.0.1', $this->irbisServerPort, '1', '1', $this->DB_RDRKV);
            
            if ($irbis->login()) {
                try {
                    $complRec = $irbis->records_search('I='.$newComplectNumber, 10, 1, $format = '@all');
                    
                    if (!empty($complRec['records'])) {
                        $mfn = $complRec['records'][0][0];
                        $record = $irbis->record_read($mfn);
                        
                        if (is_object($record)) {
                            $fieldCount = $record->getFieldCount(1033);
                            for ($i = 1; $i <= $fieldCount; $i++) {
                                $fieldValue = $record->getField(1033, $i, '*');
                                if (!empty($fieldValue)) {
                                    $thisComplect[] = $fieldValue;
                                }
                            }
                        }
                    }
                    $irbis->logout();
                } catch (Exception $e) {
                    $irbis->logout();
                }
            }
        }
        
        return view('makeComplect.index', compact('makingComplect', 'newComplectNumber', 'thisComplect'));
    }

   
    public function Store(Request $request){
        
        $validated = $request->validate([
            'complID' => 'required|string',
            'invnum' => 'required|string',
        ]);
        
        $irbis = new \irbis64('127.0.0.1', $this->irbisServerPort, '1', '1', $this->DB_RDRKV);
        if ($irbis->login()) {
            try {
                //найдем запись комплекта с идентификатором complID
                $complRec = $irbis->records_search('I='.$validated['complID'], 10, 1, $format = '@all');
               
                if (empty($complRec['records'])) {
                    $irbis->logout();
                    return redirect()->route('makeComplect')->with('error', 'Комплект с номером ' . $validated['complID'] . ' не найден');
                }
                
                $mfn = $complRec['records'][0][0];
                $field_num = 1033;
                $invNumToRec = $validated['invnum'];
                $record = $irbis->record_read($mfn);
                
                if(is_object($record)){
                    // Проверяем, не добавлен ли уже этот инвентарный номер в комплект
                    $fieldCount = $record->getFieldCount(1033);
                    for ($i = 1; $i <= $fieldCount; $i++) {
                        $fieldValue = $record->getField(1033, $i, '*');
                        if ($fieldValue === $invNumToRec) {
                            $irbis->logout();
                            return redirect()->route('makeComplect')->with('error', 'Инвентарный номер ' . $invNumToRec . ' уже добавлен в комплект');
                        }
                    }

                    //проверяем, есть ли запись с инвентарным номером в БД RDRKV
                    $RDRKV_rec = $irbis->records_search('IN='.$invNumToRec, 10, 1, $format = '@all');
                    if(!empty($RDRKV_rec['records'])){
                        $irbis->logout();
                        return redirect()->route('makeComplect')->with('error', 'Инвентарный номер ' . $invNumToRec . ' уже добавлен в БД RDRKV');
                    }
                    
                    //проверим, есть ли инвентарный номер в БД IBIS
                    //если его нет в БД IBIS, то не добавляем его в комплект
                    $irbis->set_db('IBIS');
                    $IBIS_rec = $irbis->records_search('IN='.$invNumToRec, 10, 1, $format = '@all');
                    if(empty($IBIS_rec['records'])){
                        $irbis->logout();
                        return redirect()->route('makeComplect')->with('error', 'Инвентарный номер ' . $invNumToRec . ' не найден в БД IBIS');
                    }
                    $irbis->set_db($this->DB_RDRKV);

                    $record->addField($invNumToRec, $field_num);
                    $write_result = $irbis->record_write($record->getRecordArray(), false, true);
                    
                    if ($write_result !== '') {
                        $irbis->logout();
                        return redirect()->route('makeComplect')->with('error', 'Ошибка записи: ' . $irbis->error($write_result));
                    }
                    
                    $irbis->logout();
                    return redirect()->route('makeComplect')->with('success', 'Инвентарный номер ' . $invNumToRec . ' успешно добавлен в комплект');
                    
                } else {
                    $irbis->logout();
                    return redirect()->route('makeComplect')->with('error', 'Не удалось получить запись комплекта');
                }
            } catch (Exception $e) {
                $irbis->logout();
                return redirect()->route('makeComplect')->with('error', 'Ошибка при добавлении: ' . $e->getMessage());
            }
        } else {
            return redirect()->route('makeComplect')->with('error', 'Не удалось подключиться к серверу ИРБИС');
        }
    }

    public function createNewComplect(Request $request){
        
        
        $irbis = new \irbis64('127.0.0.1',  $this->irbisServerPort, '1', '1', $this->DB_RDRKV);
        
        if ($irbis->login()) {
            try {
                // Создаем новую пустую запись
                $record = new \irbisRecord();
                
                // Получаем максимальный MFN для генерации нового номера комплекта
                $maxMfn = $irbis->mfn_max();
                $newComplectNumber = $maxMfn + 1;
                
                // Добавляем основные поля для записи комплекта
                // Поле 903 - номер комплекта
                $record->addField($newComplectNumber, 903);
                                  
                // Получаем массив записи для сохранения
                $recordArray = $record->getRecordArray();
                
                // Записываем новую запись в базу
                $write_result = $irbis->record_write($recordArray, false, true);
                
                if ($write_result !== '') {
                    $irbis->logout();
                    return response()->json([
                        'success' => false,
                        'message' => 'Ошибка при создании комплекта: ' . $irbis->error($write_result)
                    ]);
                } else {
                    $irbis->logout();
                    $makingComplect = true;
                    Session::put('makingComplect', true);
                    Session::put('newComplectNumber', $newComplectNumber);
                    return view('makeComplect.index', compact('newComplectNumber', 'makingComplect'));
                }
                
            } catch (Exception $e) {
                $irbis->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при создании комплекта: ' . $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось подключиться к серверу ИРБИС'
            ]);
        }
    }

    public function closeComplect(Request $request){
        Session::forget('makingComplect');
        Session::forget('newComplectNumber');
        
        return redirect()->route('makeComplect')->with('success', 'Комплект закрыт');
    }

    public function remove(Request $request){
        
        $validated = $request->validate([
            'complID' => 'required|string',
            'invnum' => 'required|string',
        ]);
        
        $irbis = new \irbis64('127.0.0.1', $this->irbisServerPort, '1', '1', $this->DB_RDRKV);
        if ($irbis->login()) {
            try {
                // Найдем запись комплекта с идентификатором complID
                $complRec = $irbis->records_search('I='.$validated['complID'], 10, 1, $format = '@all');
                
                if (empty($complRec['records'])) {
                    $irbis->logout();
                    return redirect()->route('makeComplect')->with('error', 'Комплект с номером ' . $validated['complID'] . ' не найден');
                }
                
                $mfn = $complRec['records'][0][0];
                $invNumToRemove = $validated['invnum'];
                $record = $irbis->record_read($mfn);
                
                if (is_object($record)) {
                    $found = false;
                    $fieldCount = $record->getFieldCount(1033);
                    
                    // Получаем исходную запись
                    $recordArray = $record->getRecordArray();
                    
                    // Создаем новую запись с сохранением метаданных
                    $newRecordArray = [
                        'mfn' => $recordArray['mfn'],
                        'status' => $recordArray['status'], 
                        'ver' => $recordArray['ver'],
                        'fields' => []
                    ];
                    
                    // Копируем все поля, кроме удаляемого инвентарного номера
                    if (isset($recordArray['fields'])) {
                        foreach ($recordArray['fields'] as $fieldTag => $fieldIterations) {
                            foreach ($fieldIterations as $iteration => $fieldData) {
                                if ($fieldTag == 1033 && isset($fieldData['*']) && $fieldData['*'] == $invNumToRemove) {
                                    $found = true;
                                    continue; // Пропускаем это поле
                                }
                                if (isset($fieldData['*'])) {
                                    // Добавляем поле в новую запись
                                    if (!isset($newRecordArray['fields'][$fieldTag])) {
                                        $newRecordArray['fields'][$fieldTag] = [];
                                    }
                                    $newRecordArray['fields'][$fieldTag][] = $fieldData;
                                }
                            }
                        }
                    }
                    
                    if (!$found) {
                        $irbis->logout();
                        return redirect()->route('makeComplect')->with('error', 'Инвентарный номер ' . $invNumToRemove . ' не найден в комплекте');
                    }
                    
                    $write_result = $irbis->record_write($newRecordArray, true, true, $mfn);
                    
                    if ($write_result !== '') {
                        $irbis->logout();
                        return redirect()->route('makeComplect')->with('error', 'Ошибка при удалении: ' . $irbis->error($write_result));
                    }
                    
                    $irbis->logout();
                    return redirect()->route('makeComplect')->with('success', 'Инвентарный номер ' . $invNumToRemove . ' успешно удален из комплекта');
                    
                } else {
                    $irbis->logout();
                    return redirect()->route('makeComplect')->with('error', 'Не удалось получить запись комплекта');
                }
            } catch (Exception $e) {
                $irbis->logout();
                return redirect()->route('makeComplect')->with('error', 'Ошибка при удалении: ' . $e->getMessage());
            }
        } else {
            return redirect()->route('makeComplect')->with('error', 'Не удалось подключиться к серверу ИРБИС');
        }
    }
 
}
