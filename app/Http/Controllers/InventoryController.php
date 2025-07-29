<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryApproval;
use App\Models\Rastshifr;
use App\Models\StorLoc;

// Подключаем класс irbis64
require_once app_path('Http/Controllers/irbis_class.php');

class InventoryController extends Controller
{
    public function show(){
        $rastshifrs = Rastshifr::all();
        $storlocs = StorLoc::all();
        return view('inventory.index', compact('rastshifrs', 'storlocs'));
    }

    public function approveSuccess(){
        return view('approveSuccess'); 
    }

    public function approveAccepted(Request $request){
        
        $validated = $request->validate([
        'booksNum' => 'required|integer|min:1'
        ]);
        date_default_timezone_set('Europe/Moscow');
        // Создание записи в базе данных
        InventoryApproval::create([
            'labrarian' => auth()->user()->name,
            'stor_loc' => $request->input('storLoc'),
            'place_code' => $request->input('rastShifr'),
            'inv_num' => $request->input('invNum'),
            'copies_count' => $validated['booksNum'],
            'book_descr' => $request->input('bookDescr'),
            'db' => $request->input('db')
            ]);
            
            //запишем данные в БД REQREC
            $ReqRecDB = config('app.ReqRecDataBase');
            $irbisServerPort = config('app.irbisServerPort');
            $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', $ReqRecDB);
            if ($irbis->login()) {
                $day = date('Y-m-d H:i:s');
                $maxMfn = $irbis->mfn_max();
                $record = new \irbisRecord();
                $record->addField($request->input('invNum'), 903);
                $record->addField($request->input('bookDescr'), 201);
                $record->addField($day, 40);
                $record->addField($request->input('db'), 1);
                $record->addField(auth()->user()->name, 50);
                $record->addField('INV', 920);

                $recordArray = $record->getRecordArray();
                $write_result = $irbis->record_write($recordArray, false, true);
                if ($write_result !== '') {
                    dd('Ошибка записи: ' . $irbis->error($write_result));
                }

                //запишем дату инвентаризации в поле 910^S
                $date = date('Ymd');
                $invNum = $request->input('invNum');
                $barcode = $request->input('barcode');
                if($invNum == "инвентарный номер не найден"){                   
                    $this->updateBookInventarisationDate($barcode, $date, $irbis);
                }else{
                    $this->updateBookInventarisationDate($invNum, $date, $irbis);
                }
                

                $irbis->logout();
            }else{
                dd("Не удалось подключиться к серверу ИРБИС");
            }
        return view('inventory.invApproved');
    }

    public function updateBookInventarisationDate($inventNum, $date, $irbis)
    {
        //dd($inventNum);
        $irbis->set_db('IBIS');
        
        $bookRecord = $irbis->records_search('IN='.$inventNum, 10, 1, $format = '@all');
        
        if (!empty($bookRecord['records'][0])) {
            
            $mfn = $bookRecord['records'][0][0];

            $record = $irbis->record_read($mfn);
            $rec = $record->getRecordArray();
            $c = 1;
            foreach($rec['fields']["910"] as $field){
                //echo $c . " " . $field["B"] . " " . $field["H"] . "<br>";
                if(isset($field["B"])){
                    if($field["B"] == $inventNum){
                        $record->setField($date, 910, $c, 'S');
                    }
                }
                if(isset($field["H"])){
                    if($field["H"] == $inventNum){
                        $record->setField($date, 910, $c, 'S');
                    }
                }
                $c++;
            
            }
            
            $irbis->record_write($record->getRecordArray(), false, true);
            //dd($rec['fields']["910"]);
            
        } else {
            echo "Запись не найдена";
        }
        
        
    }

    public function invFind(Request $request){
        
        $librarian = auth()->user()->name;
        global $invNumFromDB;
         $irbisServerPort = config('app.irbisServerPort');
       

         $validated = $request->validate([
            'db' => 'required|string',
            'invNum' => 'required|string',
            'booksNum' => 'required|string',
            'storLoc' => 'required|string',
            'rastShifr' => 'required|string',
        ]);

        $usersRastShifr = $validated['rastShifr'];
        $usersStorLoc = $validated['storLoc'];
        
        $db = $validated['db'];
        $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', $db);
        if ($irbis->login()) {
            $book = $irbis->records_search('IN='.$validated['invNum'], 10, 1, $format = '@all');//для вывода инфо о книге
                
                if(!isset($book['records'])){//если запись не найдена по IN= ищем по INS=
                    $book = $irbis->records_search('INS='.$validated['invNum'], 10, 1);
                    $pref = 'INS=';
                    if(!isset($book['records'])){//если запись не найдена по INS= ищем по EXU=
                        $book = $irbis->records_search('EXU='.$validated['invNum'], 10, 1);
                        $pref = 'EXU=';
                        if(!isset($book['records'])){//запись не найдена
                            //dd("запись не найдена по префиксам IN, INS, EXU");
                            $invNum = $validated['invNum'];
                            return view('inventory.recNotFound', compact('invNum'));
                        }
                    }
                }
                
            $bookShortRec = $irbis->records_search('IN='.$validated['invNum'], 10, 1, $format = '@brief_ik');
                if(!isset($bookShortRec['records'])){//если запись не найдена по IN= ищем по INS=
                    $bookShortRec = $irbis->records_search('INS='.$validated['invNum'], 10, 1);
                    $pref = 'INS=';
                    if(!isset($bookShortRec['records'])){//если запись не найдена по INS= ищем по EXU=
                        $bookShortRec = $irbis->records_search('EXU='.$validated['invNum'], 10, 1);
                        $pref = 'EXU=';
                        if(!isset($bookShortRec['records'])){//запись не найдена
                            //dd("запись не найдена по префиксам IN, INS, EXU");
                            return view('inventory.recNotFound');
                        }
                    }
                }
               
            $bookDescr = $bookShortRec['records'][0][1];
            //dd($bookDescr);    
        
            //dd($book['records'][0]);
            if(isset($book['records'])){                    
                    foreach($book['records'][0] as $record){
                       //dd($book['records'][0]);
                       
                        $invNum = $validated['invNum'];
                        $found = $this -> isInvNum($record, $invNum, $invNumFromDB);//проверяем содержит ли запись инвентарный номер
                        
                        if($found!==false){
                            $found2 = strpos($record, "910/");//найдем запись экземпляра
                            if($found2!==false){
                                //echo $record . "<br>";//////////////////////////////////////////вся запись целиком
                                $bookFound = $record;//запись книги, для которой нужно вывести статус
                                //dd($bookFound);
                            }else{                                
                                $found940 = strpos($record, "940/");//запись найдена в поле 940?
                                if($found940!==false){
                                    $bookFound = "spisan";//книга списана 
                                }
                            }
                        break;/////////////////////////////////////////////////книга найдена
                        }
                    }
                }else{
                    dd("book not found");
                    echo "<h1>Не удалось получить всю запись</h1>";
                    echo "<pre>";
                    echo "краткая запись в формате brief:<br>";
                    var_dump($res);
                    echo "вся запись в формате all:<br>";
                    var_dump($resAll);
                    echo "</pre>";
                } 
                //dd($bookFound);
                //////////место хранения
                if(isset($bookFound)){
                    //$storLocFound = $this->getStorLoc($book, $validated['invNum'], $usersStorLoc, $irbis);
                    //$rastShifrFound = $this->getRastShifr($book, $validated['invNum'], $usersRastShifr, $irbis);
                    //$bookStatus = $this->getBookStatus($book, $validated['invNum'], $irbis);
                }else{
                    $storLocFound = [
                        'status' => false,
                        'storLoc' => "не определен, экземпляр утерян или списан"
                    ];
                    $rastShifrFound = [
                        'status' => false,
                        'rastShifr' => "не определен, экземпляр утерян или списан"
                    ];
                }
                $storLocFound = $this->getStorLoc($book, $validated['invNum'], $usersStorLoc, $irbis);
                $rastShifrFound = $this->getRastShifr($book, $validated['invNum'], $usersRastShifr, $irbis);
                $bookStatus = $this->getBookStatus($book, $validated['invNum'], $irbis);
                $invNum = $validated['invNum'];
                $barcode = $this->getBarcode($invNum, $irbis, $book);               
                $invStatus = $this->getInventoryStatus($invNum);            
                $invDate = $this->getInvDate($invNum, $irbis, $book);
                $invNum = $this->getInvNum($invNum, $irbis, $book);

                $rastshifrs = Rastshifr::all();
                $storlocs = StorLoc::all();
                return view('inventory.index', compact('bookDescr', 'storLocFound', 'rastShifrFound', 'invNum', 'invStatus', 'db', 'bookStatus', 'invDate', 'barcode', 'rastshifrs', 'storlocs'));
        }else{
            echo '<h3 class="text-danger" style="margin-left:20%">Не удалось подключиться к серверу ИРБИС</h3>';
        }
    }//////////////////////////end of invFind()

    public function getBarcode($invNum, $irbis, $book){
    
        $mfn = $book['records'][0][0];
        $record = $irbis->record_read($mfn);
        $barcode = "штрихкод не найден";
        
        foreach($record->record['fields'][910] as $field){
            // Поиск штрихкода по инвентарному номеру (B -> H)
            if(isset($field["H"]) && isset($field["B"]) && $field["B"] == $invNum){                              
                $barcode = $field["H"];
                break;
            }
            // Поиск штрихкода по штрихкоду (если ввели штрихкод, возвращаем его же)
            if(isset($field["H"]) && $field["H"] == $invNum){
                $barcode = $field["H"];
                break;
            }
        }
        return $barcode;
    }
    
    public function getInvNum($invNum, $irbis, $book){
        $mfn = $book['records'][0][0];
        $record = $irbis->record_read($mfn);
        $foundInvNum = "инвентарный номер не найден";
        
        foreach($record->record['fields'][910] as $field){
            // Поиск инвентарного номера по инвентарному номеру (B -> B)
            if(isset($field["B"]) && $field["B"] == $invNum){                
                $foundInvNum = $field["B"]; 
                break;
            }
            // Поиск инвентарного номера по штрихкоду (H -> B)
            if(isset($field["H"]) && isset($field["B"]) && $field["H"] == $invNum){
                $foundInvNum = $field["B"];
                break;
            }
        }
        
        return $foundInvNum;
    }

    public function getInvDate($invNumber, $irbis, $book){
       /* $record = InventoryApproval::where('inv_num', $invNumber)->first();
        $invDate = $record->created_at;
        $a = explode(" ", $invDate);
        $invDate = $a[0];
        return $invDate;*/
        $mfn = $book['records'][0][0];
        $record = $irbis->record_read($mfn);
        
        foreach($record->record['fields'][910] as $field){
            
            if(isset($field["B"]) && $field["B"] == $invNumber){
                
                if(isset($field["S"])){                
                    $invDate = $field["S"];
                    //переведем дату в формат dd.mm.yyyy
                    $year = substr($invDate, 0, 4);
                    $month = substr($invDate, 4, 2);
                    $day = substr($invDate, 6, 2);
                    $invDate = $day . "." . $month . "." . $year;
                }else{
                    $invDate = " не найдена";
                }                
            }
            if(isset($field["H"]) && $field["H"] == $invNumber){
                if(isset($field["S"])){                
                    $invDate = $field["S"];
                    //переведем дату в формат dd.mm.yyyy
                    $year = substr($invDate, 0, 4);
                    $month = substr($invDate, 4, 2);
                    $day = substr($invDate, 6, 2);
                    $invDate = $day . "." . $month . "." . $year;
                }else{
                    $invDate = " не найдена";
                }                
            }
            
        }
        
        return $invDate;    
    }

    public function getInventoryStatus($invNumber){//проверим прошла ли книга инвентаризацию
        $record = InventoryApproval::where('inv_num', $invNumber)->first();
        if($record){
            $status = true; //книга прошла инвентаризацию
        }else{
            $status = false;// книга не прошла инвентаризацию
        }
        return $status;
    }

    public function getRastShifr($book, $invNum, $usersRastShifr, $irbis){
        $rastShifrAndStatus = Array();        
        $mfn = $book['records'][0][0];
        $record = $irbis->record_read($mfn);
        
        foreach($record->record['fields'][910] as $field){
            if(isset($field["B"])){
                if($field["B"] == $invNum){
                    if(isset($field["R"])){
                        $rastShifrFound = $field["R"];
                    }else{
                        $rastShifrFound = "расстановочный шифр не найден";
                    }
                    break;
                }
            }
        
            if(isset($field["H"])){
                if($field["H"] == $invNum){
                    if(isset($field["R"])){
                        $rastShifrFound = $field["R"];
                    }else{
                        $rastShifrFound = "место хранения не найдено";
                    }
                }
            }
        } 
        //если расстановочный шифр совпадает с тем, что ввел пользователь, то статус true, иначе false
        if($rastShifrFound == $usersRastShifr){
            $rastShifrAndStatus['status'] = true;
        }else{  
            $rastShifrAndStatus['status'] = false;
        }
        $rastShifrAndStatus['rastShifr'] = $rastShifrFound;
        //dd($rastShifrAndStatus);
        return $rastShifrAndStatus;
    }
    
    public function getStorLoc($book, $invNum, $usersStorLoc, $irbis){
        $storLocAndStatus = Array();        
        $mfn = $book['records'][0][0];
        $record = $irbis->record_read($mfn);
        
        foreach($record->record['fields'][910] as $field){
            //dd($record->record['fields'][910]);
            if(isset($field["B"])){
                if($field["B"] == $invNum){
                    if(isset($field["D"])){
                        $storLocFound = $field["D"];
                    }else{
                        $storLocFound = "место хранения не найдено";
                    }
                }
            }
            if(isset($field["H"])){
                if($field["H"] == $invNum){
                    if(isset($field["D"])){
                        $storLocFound = $field["D"];
                    }else{
                        $storLocFound = "место хранения не найдено";
                    }
                }
            }
        } 
        //если расстановочный шифр совпадает с тем, что ввел пользователь, то статус true, иначе false
        if($storLocFound == $usersStorLoc){
            $storLocAndStatus['status'] = true;
        }else{  
            $storLocAndStatus['status'] = false;
        }
        $storLocAndStatus['storLoc'] = $storLocFound;
        //dd($storLocAndStatus);
        return $storLocAndStatus;
    }

    public function getBookStatus($book, $invNum, $irbis){
        
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

        $mfn = $book['records'][0][0];        
        $record = $irbis->record_read($mfn);
        
        foreach($record->record['fields'][910] as $field){
            if(isset($field["B"])){
                if($field["B"] == $invNum){
                    $bookStatus = $field["A"];
                    break;
                }
            }
            if(isset($field["H"])){
                if($field["H"] == $invNum){
                    $bookStatus = $field["A"];
                    break;
                }
            }
           
        }
        $bookStatus = $status[$bookStatus];
        return $bookStatus;
    }

    public function isInvNum($record, $invNum, &$invNumFromDB) {
        $is910 = strpos($record, "910/");
        if ($is910 !== false) {
            // Find the position of ^B - this is where the inventory number starts
            $isB = strpos($record, "^B");
            if ($isB === false) {
                return false; // If ^B is not found, return false
            }
        
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
            /* echo "<br>";//////////////////////////////////отладка
            echo "инв из БД: ".$invNumFromRecString;
            echo "<br>";
            echo "искомый инв: ".$invNum;
            echo "<br>";*/
            if($invNumFromRecString==$invNum){
                return true;
            }
        
        }
        return false; // If 910/ is not found, return false
    }

    public function storeRastshifr(Request $request)
    {
        $validated = $request->validate([
            'rastshifr' => 'required|string|max:255|unique:rastshifrs,rastshifr'
        ], [
            'rastshifr.required' => 'Расстановочный шифр обязателен для заполнения',
            'rastshifr.unique' => 'Такой расстановочный шифр уже существует',
            'rastshifr.max' => 'Расстановочный шифр не должен превышать 255 символов'
        ]);

        try {
            $rastshifr = Rastshifr::create([
                'rastshifr' => $validated['rastshifr']
            ]);

            return response()->json([
                'success' => true,
                'rastshifr' => $rastshifr,
                'message' => 'Расстановочный шифр успешно добавлен'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeStorloc(Request $request)
    {
        $validated = $request->validate([
            'storloc' => 'required|string|max:255|unique:storlocs,storloc',
            'storlocdescr' => 'required|string|max:255'
        ], [
            'storloc.required' => 'Краткое обозначение обязательно для заполнения',
            'storloc.unique' => 'Такое краткое обозначение уже существует',
            'storloc.max' => 'Краткое обозначение не должно превышать 255 символов',
            'storlocdescr.required' => 'Полное описание обязательно для заполнения',
            'storlocdescr.max' => 'Полное описание не должно превышать 255 символов'
        ]);

        try {
            $storloc = StorLoc::create([
                'storloc' => $validated['storloc'],
                'storlocdescr' => $validated['storlocdescr']
            ]);

            return response()->json([
                'success' => true,
                'storloc' => $storloc,
                'message' => 'Место хранения успешно добавлено'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении: ' . $e->getMessage()
            ], 500);
        }
    }
}
