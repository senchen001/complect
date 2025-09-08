<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\borrowedBook;


// Подключаем класс irbis64
require_once app_path('Http/Controllers/irbis_class.php');

class SearchController extends Controller
{
    public function search(Request $request)
    {
        
        global $invNumFromDB;
        $pref = "IN=";//префикс по умолчанию
        $irbisServerPort = config('app.irbisServerPort');
        $irbisServerHost = config('app.irbisServerHost');

        $validated = $request->validate([
            'inputNumber' => 'required|string',
            'calendar_date' => 'nullable|string',
            'pickup_location' => 'nullable|string',
        ]);
        
        // Сохраняем дату календаря в сессии, если она была передана
        if (!empty($validated['calendar_date'])) {
            session(['returnDate' => $validated['calendar_date']]);
        }
        
        // Сохраняем место выдачи в сессии, если оно было передано
        if (!empty($validated['pickup_location'])) {
            session(['pickupLocation' => $validated['pickup_location']]);
        }
        
        $irbis = new \irbis64($irbisServerHost, $irbisServerPort, '1', '1', 'IBIS');
        if ($irbis->login()) {
        
                $res = $irbis->records_search('IN='.$validated['inputNumber'], 10, 1, $format = '@brief_ik');//для вывода инфо о книге
                if(!isset($res['records'])){//если запись не найдена по IN= ищем по INS=
                    $res = $irbis->records_search('INS='.$validated['inputNumber'], 10, 1, $format = '@brief_ik');
                    $pref = 'INS=';
                    if(!isset($res['records'])){//если запись не найдена по INS= ищем по EXU=
                        $res = $irbis->records_search('EXU='.$validated['inputNumber'], 10, 1, $format = '@brief_ik');
                        $pref = 'EXU=';
                        if(!isset($res['records'])){//запись не найдена
                            //dd("запись не найдена по префиксам IN, INS, EXU");
                            $invNum = $validated['inputNumber'];
                            return view('recNotFound', compact('invNum'));
                        }
                    }
                }         
                $resAll = $irbis->records_search($pref.$validated['inputNumber'], 10, 1, $format = '@all');//для вывода инфо о статусе
                
                if(isset($resAll['records'])){                    
                    foreach($resAll['records'][0] as $record){
                     
                        $invNum = $validated['inputNumber'];
                        $found = $this -> isInvNum($record, $invNum, $invNumFromDB);//проверяем содержит ли запись инвентарный номер
                        if($found!==false){
                            
                            $found2 = strpos($record, "910/");//найдем запись экземпляра
                            if($found2!==false){
                                //echo $record . "<br>";//////////////////////////////////////////вся запись целиком
                                $book = $record;//запись книги, для которой нужно вывести статус
                            }else{                                
                                $found940 = strpos($record, "940/");//запись найдена в поле 940?
                                if($found940!==false){
                                    $book = "spisan";//книга списана 
                                }
                            }
                        break;/////////////////////////////////////////////////книга найдена
                        }
                    }
                }else{
                    echo "<h1>Не удалось получить всю запись</h1>";
                    echo "<pre>";
                    echo "краткая запись в формате brief:<br>";
                    var_dump($res);
                    echo "вся запись в формате all:<br>";
                    var_dump($resAll);
                    echo "</pre>";
                }   
                
            //dd($resAll['records']);
            
        }else{
            echo '<h3 class="text-danger" style="margin-left:20%">Не удалось подключиться к серверу ИРБИС</h3>';
        }
        if(isset($book)){
            if($book != "spisan"){
                $bookStatus = $this->getBookStatus($book);
                //echo "<br>----------------".$bookStatus."<br>";
            }
            if($book == "spisan"){
                $bookStatus = "Архивные сведения списание (940)";
            }
        }else{
            $invNum = $validated['inputNumber'];
            return view('recNotFound', compact('invNum'));
            //dd("no book");
        }

        // Получаем штрих-код для найденной записи
        $barcode = $this->getBarcode($validated['inputNumber'], $irbis, $resAll);
        //получаем обложку для найденной записи
        $cover = $this->getCover($validated['inputNumber'], $irbis);
        
        $result = $res;

        //////////////////////////////////////////////////////////// Соберем комплект
        $complectDB = config('app.complectDataBase');
        
        $irbis->set_db($complectDB);
                
        $res2 = $irbis->records_search('IN='.$invNumFromDB,  10, 1);//инвентарные номера записей в комплекте
        if(empty($res2['records'])){
            //если не нашли по инвентарному номеру, то ищем по штрих-коду
            $res2 = $irbis->records_search('IN='.$barcode,  10, 1);
            
        }
        //dd($res2['records'][0][1]);
        $complectRecs = Array();
        
        $irbis->set_db('IBIS');

        $complect = Array();
        
        if(isset($res2['records'][0][1])){
            $complect = explode("*", $res2['records'][0][1]);
            for($i=0; $i<count($complect)-1; $i++){                
                $res = $irbis->records_search('IN='.$complect[$i], 10, 1, $format = '@brief_ik');
                if(isset($res['records'][0][1])){
                    $cover2 = $this->getCover($complect[$i], $irbis);
                    $complectRecs[] = $res['records'][0][1] . " <br> ".$complect[$i] . " <br> " . $cover2;//в массиве записи книг, которые входят в комплект
                }else{
                    dd("проверьте запись с комплектами в БД RDRKV2");
                }
            }
        }
        
        // возвращаем статус комплекта - выдан ли он читателю
        if(count($complect) > 1){
            $complectStatus = $this->getComplectStatus($complect, $irbis);
        }else{
            $complectStatus = 1; //экземпляр не состоит в комплекте
        }

        // Возвращаем шаблон с результатом
        if(isset($bookStatus)){
            return view('search', compact('result', 'complectRecs', 'bookStatus', 'invNum', 'invNumFromDB', 'barcode', 'complectStatus', 'cover'));
        }else{
            
            return view('search', compact('result', 'complectRecs', 'invNum', 'barcode', 'cover'));
        }
    }

    public function getCover($inputNumber, $irbis){
        $resAll = $irbis->records_search('IN='.$inputNumber, 10, 1, $format = '@all');
        $mfn = $resAll['records'][0][0];
        $record = $irbis->record_read($mfn);
        if(isset($record->record['fields'][953][1]['T'])){
            $cover = $record->record['fields'][953][1]['T'];
        }else{
            $cover = "defaultCover.jpg";
        }
        return $cover;
    }

    public function getBarcode($inputNumber, $irbis, $book){
        $mfn = $book['records'][0][0];
        $record = $irbis->record_read($mfn);
        $barcode = "штрихкод не найден";
        
        global $invNumFromDB;
        
        foreach($record->record['fields'][910] as $field){
            // Ищем запись с нужным инвентарным номером и извлекаем штрих-код
            if(isset($field["B"]) && $field["B"] == $invNumFromDB && isset($field["H"])){
                $barcode = $field["H"];
                break;
            }
            // Если ввели штрих-код, проверяем этот штрих-код
            if(isset($field["H"]) && $field["H"] == $inputNumber){
                $barcode = $field["H"];
                break;
            }
        }
        return $barcode;
    }


    public function getComplectStatus($complect, $irbis){
        
        /*$borrowedBook = borrowedBook::where('inv_num', $complect[0])->first();
        if($borrowedBook){
            if($borrowedBook->inv_num == $complect[0]){
                $complectStatus = false; // комплект выдан читателю
            }else{
                $complectStatus = true; //комплек доступен для выдачи
            }
        }else{
            $complectStatus = true; //комплек доступен для выдачи
        }*/
///////////////////выясним выдан ли какой-то из экземпляров комплекта читателю
        $complectStatus = true;
        for($i=0; $i<count($complect)-1; $i++){
            $res = $irbis->records_search('IN='.$complect[$i], 10, 1,$format = '@all');
            $mfn = $res['records'][0][0];
            $record = $irbis->record_read($mfn);
            //dd($record);
            foreach($record->record['fields'][910] as $field){
                //dd($field['B']);
                if(isset($field['B'])){
                    if($field['B'] == $complect[$i]){
                        //echo $field['A'] . "<br>";
                        if($field['A'] == 1){//если статус выдан читателю, то комплект не доступен для выдачи
                            $complectStatus = false;
                            break;
                    }
                }
            }
            //dd($record);
            }        
    }
    //dd($complectStatus);
    return $complectStatus;
}

    public function isInvNum($record, $invNum, &$invNumFromDB) {
        $is910 = strpos($record, "910/");
        if ($is910 !== false) {
            // Сначала проверяем поиск по инвентарному номеру (^B)
            $isB = strpos($record, "^B");
            if ($isB !== false) {
                $startPos = $isB + 2;
                $invNumFromRec = Array();
                
                // Loop through the record starting from the position after ^B
                for ($i = $startPos; $i < strlen($record); $i++) {
                    // The inventory number ends with ^
                    if ($record[$i] == "^") {
                        break; // Exit the loop if we reach the end of the inventory number
                    }
                    $invNumFromRec[] = $record[$i];
                }
                
                // Convert the array to a string if needed
                $invNumFromRecString = implode('', $invNumFromRec);
                $invNumFromDB = $invNumFromRecString;
                
                if($invNumFromRecString == $invNum){
                    return true;
                }
            }
            
            // Теперь проверяем поиск по штрих-коду (^H)
            $isH = strpos($record, "^H");
            if ($isH !== false) {
                $startPos = $isH + 2;
                $barcodeFromRec = Array();
                
                // Loop through the record starting from the position after ^H
                for ($i = $startPos; $i < strlen($record); $i++) {
                    // The barcode ends with ^
                    if ($record[$i] == "^") {
                        break; // Exit the loop if we reach the end of the barcode
                    }
                    $barcodeFromRec[] = $record[$i];
                }
                
                // Convert the array to a string if needed
                $barcodeFromRecString = implode('', $barcodeFromRec);
                
                if($barcodeFromRecString == $invNum){
                    // Если нашли по штрих-коду, нужно найти соответствующий инвентарный номер
                    if ($isB !== false) {
                        $startPos = $isB + 2;
                        $invNumFromRec = Array();
                        
                        for ($i = $startPos; $i < strlen($record); $i++) {
                            if ($record[$i] == "^") {
                                break;
                            }
                            $invNumFromRec[] = $record[$i];
                        }
                        
                        $invNumFromDB = implode('', $invNumFromRec);
                    } else {
                        $invNumFromDB = $barcodeFromRecString; // Используем штрих-код как ID
                    }
                    return true;
                }
            }
        }
        return false; // If 910/ is not found, return false
    }


    public function getBookStatus($book){
        $status = Array(
            "0" => "Для ЭК - отдельный экземпляр, поступил по месту хранения",
            "R" => "Для ЭК - группа экз-ров, Размножение с вводом инвентарных номеров",
            "U" => "Для ЭК ВУЗа - группа экз-ров (Безинв. учет). Размножение не требуется",
            "C" => "Группа экземпляров для библиотеки сети. Размножение не требуется",
            "E" => "Сетевой локальный ресурс",
            "8" => "Номер журнала/газеты поступил, но еще не дошел до места хранения",
            "2" => "Отдельный экземпляр в библиотеку еще не поступал, ожидается",
            "3" => "В переплете",
            "4" => "Утерян",
            "5" => "Временно не выдается",
            "6" => "Списан",
            "p" => "Номер журнала/газеты переплетен (входит в подшивку)",
            "1" => "Выдан читателю",
            "9" => "На бронеполке"
        );

        $statPos = strpos($book, "^A");
        if($statPos!==false){
        //echo "<br>-----------statPos ".$statPos . "<br>";//////////////отладка
        //echo $book[$statPos+2] . "<br>";//позиция статуса в строке
        
        $bookStat = $status[$book[$statPos+2]];
        }
        else{
           // dd($book);
        }
        return $bookStat;
    }
}
