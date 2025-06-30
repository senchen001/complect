<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class giveComplectController extends Controller
{
    public function giveComplect(Request $request)
    {
        $books = Array();
        $inventNums = Array();
        $booksAmount = $request->booksAmount;//колличество книг в риквесте
        
        for($bookNum=1; $bookNum < $booksAmount; $bookNum++){        
            $book = "book".$bookNum;
            $books[] = $request->$book;//сложим записи книг в массив
        }
        //соберем инвентарники в массив
        foreach($books as $book){
            $rec = explode(":", $book);//инвентарн номер лежит в конце строки после :
            $arr_len = count($rec);
            $inventNums[] = trim($rec[$arr_len-1]);
        }
        foreach($inventNums as $iNum){
            echo $iNum . "<br>";
        }
        echo "дата возврата: " . $request->day . "<br>";
        echo "дата выдачи: " . date('Y-m-d');
        $returnDate = $request->day;
        $giveDate = date('Y-m-d');
        $irbisDates = $this->dateToIrbisDate($giveDate, $returnDate);
    }

    public function dateToIrbisDate($giveDate, $returnDate){
        $dates = Array(); // массив для дат в формате Ирбиса
        //дата выдачи имеет вид 2025-06-30
        //уберем "-" и склеим массив
        $giveD = explode("-", $giveDate);
        $giveD = implode("", $giveD);
        $dates[] = $giveD;
        
        //дата возврата приходит в виде 30.06.2025
        //поменяем день и год местами и склеим массив
        $retD = explode(".", $returnDate);
        $d = $retD[0];
        $retD[0] = $retD[2];
        $retD[2] = $d;
        $retD = implode("", $retD);
        $dates[] = $retD;
        dd($dates);
    }
}
