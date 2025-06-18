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
                        $found = strpos($record, $validated['inputNumber']);//найдем строку с инвентарником
                        if($found!==false){
                            $found2 = strpos($record, "910/1:");//найдем запись экземпляра
                            if($found2!==false){
                                echo $record . "<br>";
                                $book = $record;//запись книги, для которой нужно вывести статус
                            }
                        }
                    }
                }else{
                    echo "<h1>Не удалось получить всю запись</h1>";
                }   
                
            //dd($resAll['records']);
            }
        }
        if(isset($book)){
            $bookStatus = $this->getBookStatus($book);
            echo "<br>----------------".$bookStatus."<br>";
        }

        
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
        echo "<br>-----------statPos ".$statPos . "<br>";
        echo $book[$statPos+2] . "<br>";//позиция статуса в строке
        $bookStat = $status[$book[$statPos+2]];
        return $bookStat;
    }
}
