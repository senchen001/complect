<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Подключаем класс irbis64
require_once app_path('Http/Controllers/irbis_class.php');

class SearchController extends Controller
{
    public function search(Request $request)
    {
        global $invNumFromDB;
        $pref = "IN=";//префикс по умолчанию
        $validated = $request->validate([
            'inputNumber' => 'required|string',
        ]);
        $irbis = new \irbis64('127.0.0.1', 6666, '1', '1', 'IBIS');
        if ($irbis->login()) {
            //echo "Logged in successfully.<br>";
            
                $res = $irbis->records_search('IN='.$validated['inputNumber'], 10, 1);//для вывода инфо о книге
                if(!isset($res['records'])){//если запись не найдена по IN= ищем по INS=
                    $res = $irbis->records_search('INS='.$validated['inputNumber'], 10, 1);
                    $pref = 'INS=';
                    if(!isset($res['records'])){//если запись не найдена по INS= ищем по EXU=
                        $res = $irbis->records_search('EXU='.$validated['inputNumber'], 10, 1);
                        $pref = 'EXU=';
                        if(!isset($res['records'])){//запись не найдена
                            dd("запись не найдена по префиксам IN, INS, EXU");
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
            
        }
        if(isset($book)){
            if($book != "spisan"){
                $bookStatus = $this->getBookStatus($book);
                //echo "<br>----------------".$bookStatus."<br>";
            }
            if($book == "spisan"){
                $bookStatus = "Архивные сведения списание (940)";
            }
        }else{dd("no book");}

        
        $result = $res;
        $irbis->set_db('RDRKV2');
        $res2 = $irbis->records_search('IN='.$validated['inputNumber'],  10, 1);//инвентарные номера записей в комплекте
        //dd($res2['records'][0][1]);
        $complectRecs = Array();
        $irbis->set_db('IBIS');

        if(isset($res2['records'][0][1])){
            $complect = explode("*", $res2['records'][0][1]);
            for($i=0; $i<count($complect)-1; $i++){
                $res = $irbis->records_search('IN='.$complect[$i], 10, 1);
                //dd($res['records'][0][1]);
                if(isset($res['records'][0][1])){
                    $complectRecs[] = $res['records'][0][1];//в массиве записи книг, которые входят в комплект
                }else{
                    dd("проверьте запись с комплектами в БД RDRKV2");
                }
            }
        }
        
        // Возвращаем шаблон с результатом
        if(isset($bookStatus)){
            return view('search', compact('result', 'complectRecs', 'bookStatus', 'invNum', 'invNumFromDB'));
        }else{
            
            return view('search', compact('result', 'complectRecs', 'invNum'));
        }
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


    public function getBookStatus($book){
        $status = Array(
            "0" => "Для ЭК - отдельный экземпляр, поступил по месту хранения",
            "R" => "Для ЭК - группа экз-ров, Размножение с вводом инвентарных номеров",
            "U" => "Для ЭК ВУЗа - группа экз-ров (Безинв. учет). Размножение не требуется",
            "С" => "Группа экземпляров для библиотеки сети. Размножение не требуется",
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
