<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\borrowedBook;
use Exception;
use Illuminate\Support\Facades\Log;

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
        
        //echo "дата возврата: " . $request->day . "<br>";
        //echo "дата выдачи: " . date('Y-m-d');
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
           // ^B - инвентарный номер
           /////////////////////////////////////////////// запишем книги на читателя
           $x = 0; //счетчик для инвентарников
           foreach($books as $book){
           $dataToRec = "^GIBIS^D".$irbisDates[0]."^E".$irbisDates[1]."^C" . $book . "^B" . $inventNums[$x];
           $record = $irbis->record_read($mfn);
           if(is_object($record)){
                $record->addField($dataToRec, $field_num);
                $write_result = $irbis->record_write($record->getRecordArray(), true, true);
                
                if ($write_result !== '') {
                    dd('Ошибка записи: ' . $irbis->error($write_result));
                }


                //$irbis->logout();
           }else{
            dd("не удалось получить запись по mfn");
           }

           //занесем данные о выданной книге в базу mysql
         /*  borrowedBook::create([
            'labrarian' => $librarian,
            'reader' => $reader,
            'db' => "IBIS",
            'inv_num' => $inventNums[$x],
            'giveDate' => $irbisDates[0],
            'returnDate' => $irbisDates[1],
            'book_descr' => $book            
            ]);*/

            // Обновляем статус книги в ИРБИС (поле 910^A = 1 - выдана читателю)
          $this->updateBookStatus($inventNums[$x], '1', $irbis);
            
            $x++;
        
        }////////////////////////////конец записи книги на читателя
        //запишем данные в БД REQREC    
        $this->recordBooksToIrbis($books, $inventNums, $irbisDates, $irbis, $librarian, $reader);    
        }else{
            echo '<h3 class="text-danger" style="margin-left:20%">Не удалось подключиться к серверу ИРБИС</h3>';
        }
        return view('givenComplectRecorded');
    }

    public function recordBooksToIrbis($books, $inventNums, $irbisDates, $irbis, $librarian, $reader){
        $irbis->set_db('REQREC2');
        date_default_timezone_set('Europe/Moscow');
        $maxMfn = $irbis->mfn_max();
        $x = 0;
        foreach($books as $book){
            //выделим из $reader $readerID и $readerDescr - описание читателя
            $readerDescr = "";
            $reader_arr = explode(" ", $reader);
            $readerID = $reader_arr[0];
            for($i=1; $i<count($reader_arr); $i++){
                $readerDescr .= $reader_arr[$i] . " ";
            }

            //добавим к дате выдачи время выдачи
            $time = date('H:i:s');

            $readerDescr = trim($readerDescr);
            $record = new \irbisRecord();
            $record->addField($readerID, 30);
            $record->addField($readerDescr, 31);
            $record->addField($inventNums[$x], 903);
            $record->addField($book, 201);
            $record->addField($irbisDates[0] . " " . $time, 41);
            $record->addField($irbisDates[1], 42);
            $record->addField($librarian, 50);
            $record->addField($maxMfn, 903);

            $recordArray = $record->getRecordArray();
            $write_result = $irbis->record_write($recordArray, false, true);
            if ($write_result !== '') {
                dd('Ошибка записи: ' . $irbis->error($write_result));
            }
            $x++;
        }
        $irbis->logout();
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

    /**
     * Обновление статуса книги в ИРБИС
     * @param string $inventNum - инвентарный номер книги
     * @param string $status - статус (1 - выдана, 0 - доступна)
     */
    public function updateBookStatus($inventNum, $status, $irbis)
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
                //echo $c . " " . $field["B"] . " " . $field["A"] . "<br>";
                if($field["B"] == $inventNum){
                    $record->setField($status, 910, $c, 'A');
                }
                $c++;
            }
            
            $irbis->record_write($record->getRecordArray(), false, true);
            //dd($rec['fields']["910"]);
            
        } else {
            echo "Запись не найдена";
        }
        
        
    }

}
