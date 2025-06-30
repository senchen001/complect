<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class giveComplectController extends Controller
{
    public function giveComplect(Request $request)
    {
        $booksAmount = $request->booksAmount;
        for($bookNum=1; $bookNum < $booksAmount; $bookNum++){
        
        $book = "book".$bookNum;
        echo $request->$book;
        }
    }
}
