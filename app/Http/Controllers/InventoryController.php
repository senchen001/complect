<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryApproval;

// Подключаем класс irbis64
require_once app_path('Http/Controllers/irbis_class.php');

class InventoryController extends Controller
{
    public function show(){
        return view('inventory.index');
    }

    public function approveSuccess(){
        return view('approveSuccess'); 
    }

    public function approveAccepted(Request $request){
        //dd($request);
        $validated = $request->validate([
        'booksNum' => 'required|integer|min:1'
        ]);
        // Создание записи в базе данных
        InventoryApproval::create([
            'labrarian' => auth()->user()->name,
            'stor_loc' => $request->input('storLoc'),
            'place_code' => $request->input('rastShifr'),
            'inv_num' => $request->input('invNum'),
            'copies_count' => $validated['booksNum'],
            'book_descr' => $request->input('bookDescr')
            ]);
        return view('inventory.invApproved');
    }

    public function invFind(Request $request){
        $librarian = auth()->user()->name;
        global $invNumFromDB;

        $validated = $request->validate([
            'db' => 'required|string',
            'storLoc' => 'required|string',
            'rastShifr' => 'required|string',
            'invNum' => 'required|string',
            'booksNum' => 'required|string',
        ]);
        

        $irbis = new \irbis64('127.0.0.1', 6666, '1', '1', 'IBIS');
        if ($irbis->login()) {
            $book = $irbis->records_search('IN='.$validated['invNum'], 10, 1, $format = '@all');//для вывода инфо о книге
                if(!isset($book['records'])){//если запись не найдена по IN= ищем по INS=
                    $book = $irbis->records_search('INS='.$validated['invNum'], 10, 1);
                    $pref = 'INS=';
                    if(!isset($book['records'])){//если запись не найдена по INS= ищем по EXU=
                        $book = $irbis->records_search('EXU='.$validated['invNum'], 10, 1);
                        $pref = 'EXU=';
                        if(!isset($book['records'])){//запись не найдена
                            dd("запись не найдена по префиксам IN, INS, EXU");
                        }
                    }
                }
                
            $bookShortRec = $irbis->records_search('IN='.$validated['invNum'], 10, 1);
                if(!isset($bookShortRec['records'])){//если запись не найдена по IN= ищем по INS=
                    $bookShortRec = $irbis->records_search('INS='.$validated['invNum'], 10, 1);
                    $pref = 'INS=';
                    if(!isset($bookShortRec['records'])){//если запись не найдена по INS= ищем по EXU=
                        $bookShortRec = $irbis->records_search('EXU='.$validated['invNum'], 10, 1);
                        $pref = 'EXU=';
                        if(!isset($bookShortRec['records'])){//запись не найдена
                            dd("запись не найдена по префиксам IN, INS, EXU");
                        }
                    }
                }
               
            $bookDescr = $bookShortRec['records'][0][1];
            //dd($bookDescr);    

            //dd($book['records'][0]);
            if(isset($book['records'])){                    
                    foreach($book['records'][0] as $record){
                     
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
                $storLocFound = $this->getStorLoc($validated['storLoc'], $bookFound);
                $rastShifrFound = $this->getRastShifr($bookFound);
                $invNum = $validated['invNum'];
                return view('inventory.invApprove', compact('bookDescr', 'storLocFound', 'rastShifrFound', 'invNum'));
        }
    }//////////////////////////end of invFind()

    public function getRastShifr($bookfound){
        $RPos = strpos($bookfound, "^R");//найдем позицию ^R, там хранится расстановочный шифр
        
        if($RPos!==false){
            $bookfound = mb_str_split($bookfound);
            $rastShifrFound = Array();
            for($x = $RPos; $x < count($bookfound); $x++){
                if($bookfound[$x] == "^" || $bookfound[$x] == "\\"){
                    break;
                }
                $rastShifrFound[] = $bookfound[$x];
            }    
        }else{
            $rastShifrFound = "расстановочный шифр не найден";
        }
        $rastShifrFound = implode("", $rastShifrFound);
        return $rastShifrFound;
    }
    
    public function getStorLoc($storloc, $bookFoundRec){
        $DPos = strpos($bookFoundRec, "^D");//найдем позицию ^D, там храниться storLoc
        $bookFoundRec = mb_str_split($bookFoundRec);//сделаем из строки массив
        if($DPos!==false){
            $foundStorLoc = Array();
                for($x = $DPos+2; $x < count($bookFoundRec)-1; $x++){
                    if($bookFoundRec[$x]=="\\" || $bookFoundRec[$x]=="^"){
                        break;
                    }
                $foundStorLoc[] = $bookFoundRec[$x];
                }
            $foundStorLoc = implode("", $foundStorLoc);
        }else{
            $foundStorLoc = "место хранения не найдено";
        }
        return $foundStorLoc;
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
}
