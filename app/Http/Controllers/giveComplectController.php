<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

require_once app_path('Http/Controllers/irbis_class.php');

class giveComplectController extends Controller
{
    public function giveComplect(Request $request)
    {
        $irbisServerPort = config('app.irbisServerPort');
        $books = Array();
        $inventNums = Array();
        $librarian = $request->librarian;
        $reader = $request->reader;


        $booksAmount = $request->booksAmount;//колличество книг в риквесте
        
        for($bookNum=1; $bookNum < $booksAmount; $bookNum++){        
            $book = "book".$bookNum;
            $books[] = $request->$book;//сложим записи книг в массив
        }
        //соберем инвентарники в массив
        foreach($books as $book){
            $rec = explode(":", $book);//инвентарн номер лежит в конце строки после :
            $arr_len = count($rec);
            $inventNums[] = trim($rec[$arr_len-1]);//массив с инвентарными номерами
        }
        foreach($inventNums as $iNum){
            echo $iNum . "<br>";
        }
        echo "дата возврата: " . $request->day . "<br>";
        echo "дата выдачи: " . date('Y-m-d');
        $returnDate = $request->day;
        $giveDate = date('Y-m-d');
        $irbisDates = $this->dateToIrbisDate($giveDate, $returnDate);//в массиве дата выдачи и дата возврата
        /////////////////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////      запишем книги на читателя
        $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', 'RDR');
        if ($irbis->login()) {
            //найдем запись читателя по ID
           $reader_arr = explode(" ", $reader);
           $readerID = $reader_arr[0];
            
           $readerRec = $irbis->records_search('RI='.$readerID, 10, 1, $format = '@all');
           
           $mfn = $readerRec['records'][0][0];
           $field_num = 40;
           //сформируем строку для записи
           // ^G - база IBIS
           // ^D - дата выдачи
           // ^E - дата возврата
           // ^C - Книга
           $dataToRec = "^GIBIS^D".$irbisDates[0]."^E".$irbisDates[1]."^C" . $books[0];
           $record = $irbis->record_read($mfn);
           if(is_object($record)){
                $record->addField($dataToRec, $field_num);
                $write_result = $irbis->record_write($record->getRecordArray(), true, true);
                
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

    }

    public function dateToIrbisDate($giveDate, $returnDate){
        $dates = Array(); // массив для дат в формате Ирбиса
        //дата выдачи имеет вид 2025-06-30
        //уберем "-" и склеим массив
        $giveD = explode("-", $giveDate);
        $giveD = implode("", $giveD);
        $dates[] = $giveD;//значала в массив положим дату выдачи
        
        //дата возврата приходит в виде 30.06.2025
        //поменяем день и год местами и склеим массив
        $retD = explode(".", $returnDate);
        $d = $retD[0];
        $retD[0] = $retD[2];
        $retD[2] = $d;
        $retD = implode("", $retD);
        $dates[] = $retD;//положим в массив дату возврата
        return $dates;
    }
}
