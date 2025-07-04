<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

require_once app_path('Http/Controllers/irbis_class.php');

class makeComplectController extends Controller
{
    public function Show(){
        $irbisServerPort = config('app.irbisServerPort');
    $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', 'RDRKV2');
    
    $records_with_field1033 = [];
    
    if ($irbis->login()) {
        // Получаем максимальный MFN в базе
        $maxMfn = $irbis->mfn_max();
        $complects = [];
        $complect = [];
        if ($maxMfn) {
           
            for ($mfn = 1; $mfn <= $maxMfn-1; $mfn++) {
                $record = $irbis->record_read($mfn);
                
                
                if ($record && is_object($record)) {
                    
                    // Проверяем количество повторений поля 1033
                    $fieldCount = $record->getFieldCount(1033);
                    
                    if ($fieldCount > 0) {
                        // Получаем все значения поля 1033
                        for ($i = 1; $i <= $fieldCount; $i++) {
                            $fieldValue = $record->getField(1033, $i, '*');
                            if (!empty($fieldValue)) {
                                $field1033_values[] = [
                                    'complNum' => $record->getField(903, 1),
                                    'occurrence' => $i,
                                    'value' => $fieldValue
                                ];
                            }
                        }
                    }
                }
            }
        }
    }
    $complects = $this->makeComplect($field1033_values);
    //dd($complects);
        return view('makeComplect.index', compact('complects'));
    }

    public function makeComplect($f1033_values){
        $complects = [];
        $y = 1;
        foreach($f1033_values as $value){
            $complNum = $value['complNum'];
            
            // Если комплекта с таким MFN еще нет, создаем его
            if (!isset($complects[$y])) {
                $complects[$y] = [];
            }
            
            // Добавляем значение в соответствующий комплект
            $complects[$y][] = $value;
            $y++;
        }
        //dd($complects);
        return $complects;
    }

    public function Store(Request $request){
         $irbisServerPort = config('app.irbisServerPort');

        $validated = $request->validate([
            'complID' => 'required|string',
            'invnum' => 'required|string',
        ]);

        $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', 'RDRKV2');
        if ($irbis->login()) {
            //найдем запись комплекта с идентификатором complID
           $complRec = $irbis->records_search('I='.$validated['complID'], 10, 1, $format = '@all');
           //$complRec = $irbis->record_read(2);
           
           $mfn = $complRec['records'][0][0];
           $field_num = 1033;
           $invNumToRec = $validated['invnum'];
           $record = $irbis->record_read($mfn);
            //dd($record);
           if(is_object($record)){
                $record->addField($invNumToRec, $field_num);
                $write_result = $irbis->record_write($record->getRecordArray(), true, true);
                //dd($write_result);
                if ($write_result !== '') {
                    dd('Ошибка записи: ' . $irbis->error($write_result));
                }
                $irbis->logout();
           }else{
            dd("не удалось получить запись по mfn");
           }
        }else{
            echo '<h3 class="text-danger" style="margin-left:20%">Не удалось подключиться к серверу ИРБИС</h3>';
        }

        return view('makeComplect.store');
    }

    /**
     * Вывод всех записей с полем 1033
     */
    public function showField1033(){
        $irbisServerPort = config('app.irbisServerPort');
        $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', 'RDRKV2');
        
        $records_with_field1033 = [];
        
        if ($irbis->login()) {
            // Получаем максимальный MFN в базе
            $maxMfn = $irbis->mfn_max();
            
            if ($maxMfn) {
                echo "<h3>Поиск записей с полем 1033. Всего записей в базе: " . $maxMfn . "</h3>";
                
                // Перебираем все записи от 1 до максимального MFN
                for ($mfn = 1; $mfn <= $maxMfn; $mfn++) {
                    $record = $irbis->record_read($mfn);
                    
                    if ($record && is_object($record)) {
                        // Получаем массив записи
                        $recordArray = $record->getRecordArray();
                        
                        // Проверяем есть ли поле 1033 в записи
                        if (isset($recordArray['fields'][1033])) {
                            $recordData = [
                                'mfn' => $record->getMFN(),
                                'status' => $recordArray['status'],
                                'version' => $recordArray['ver'],
                                'field1033' => []
                            ];
                            
                            // Собираем все вхождения поля 1033
                            foreach ($recordArray['fields'][1033] as $occurrence => $fieldData) {
                                $recordData['field1033'][] = [
                                    'occurrence' => $occurrence,
                                    'value' => $fieldData['*']
                                ];
                            }
                            
                            $records_with_field1033[] = $recordData;
                        }
                    }
                }
            }
            
            $irbis->logout();
        } else {
            echo '<h3 class="text-danger">Не удалось подключиться к серверу ИРБИС</h3>';
            return [];
        }
        
        return $records_with_field1033;
    }

    /**
     * Вывод записей с полем 1033 в веб-интерфейсе
     */
    public function displayField1033(){
        $records = $this->showField1033();
        
        echo "<h2>Записи с полем 1033 (Инвентарные номера)</h2>";
        echo "<p>Найдено записей: " . count($records) . "</p>";
        
        if (!empty($records)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>MFN</th><th>Статус</th><th>Версия</th><th>Поле 1033 (Повторение)</th><th>Значение</th></tr>";
            
            foreach ($records as $record) {
                foreach ($record['field1033'] as $field) {
                    echo "<tr>";
                    echo "<td>" . $record['mfn'] . "</td>";
                    echo "<td>" . $record['status'] . "</td>";
                    echo "<td>" . $record['version'] . "</td>";
                    echo "<td>" . $field['occurrence'] . "</td>";
                    echo "<td>" . htmlspecialchars($field['value']) . "</td>";
                    echo "</tr>";
                }
            }
            
            echo "</table>";
        } else {
            echo "<p>Записи с полем 1033 не найдены.</p>";
        }
    }
}
