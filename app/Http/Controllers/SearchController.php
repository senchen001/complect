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
       
        $validated = $request->validate([
            'inputNumber' => 'required|string',
            'selection' => 'required|string',
        ]);
        $irbis = new \irbis64('127.0.0.1', 6666, '1', '1', 'IBIS');
        if ($irbis->login()) {
            //echo "Logged in successfully.<br>";
            if($validated['selection'] == 'i'){
                $res = $irbis->records_search('I='.$validated['inputNumber'], 10, 1);
            }
            if($validated['selection'] == 'k'){
                $res = $irbis->records_search('K='.$validated['inputNumber'], 10, 1);
            }
            if($validated['selection'] == 'in'){
                $res = $irbis->records_search('IN='.$validated['inputNumber'], 10, 1);//для вывода инфо о книге
                
                $resAll = $irbis->records_search('IN='.$validated['inputNumber'], 10, 1, $format = '@all');//для вывода инфо о статусе
                if(isset($resAll['records'])){
                    
                    foreach($resAll['records'][0] as $record){
                      /*  $found = strpos($record, $validated['inputNumber']);//найдем строку с инвентарником
                        echo "<pre>";
                        var_dump($record);
                        echo "</pre>";

                       */ 
                        $invNum = $validated['inputNumber'];
                        $found = $this -> isInvNum($record, $invNum);//проверяем содержит ли запись инвентарный номер
                        if($found!==false){
                            $found2 = strpos($record, "910/");//найдем запись экземпляра
                            if($found2!==false){
                                echo $record . "<br>";
                                $book = $record;//запись книги, для которой нужно вывести статус
                            }else{
                                
                                $found940 = strpos($record, "940/");//запись найдена в поле 940?
                                if($found940!==false){
                                    $book = "spisan";//книга списана 
                                }
                            }
                        }
                        if($found == false){
                           //echo "<br>--------инвентарный номер в записи не найден<br><pre>";
                           //var_dump($record);
                           //echo "</br>";
                        }
                    }
                }else{
                    echo "<h1>Не удалось получить всю запись</h1>";
                    echo "<pre>";
                    var_dump($res);
                    var_dump($resAll);
                    echo "</pre>";
                }   
                
            //dd($resAll['records']);
            }
        }
        if(isset($book)){
            if($book != "spisan"){
                $bookStatus = $this->getBookStatus($book);
                echo "<br>----------------".$bookStatus."<br>";
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
                $complectRecs[] = $res['records'][0][1];//в массиве записи книг, которые входят в комплект
            }
        }
        
        // Возвращаем шаблон с результатом
        if(isset($bookStatus)){
            return view('search', compact('result', 'complectRecs', 'bookStatus'));
        }else{
            
            return view('search', compact('result', 'complectRecs'));
        }
    }

    public function isInvNum($record, $invNum) {
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
        echo "<br>-----------statPos ".$statPos . "<br>";
        echo $book[$statPos+2] . "<br>";//позиция статуса в строке
        $bookStat = $status[$book[$statPos+2]];
        }
        else{
           // dd($book);
        }
        return $bookStat;
    }
}
