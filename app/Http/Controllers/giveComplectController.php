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

        dd(date('Y-m-d'));
    }
}
