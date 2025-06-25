<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Подключаем класс irbis64
require_once app_path('Http/Controllers/irbis_class.php');

class InventoryController extends Controller
{
    public function show(){
        return view('inventory.index');
    }

    public function invFind(Request $request){
        $librarian = auth()->user()->name;
        
        $validated = $request->validate([
            'db' => 'required|string',
            'storLoc' => 'required|string',
            'rastShifr' => 'required|string',
            'invNum' => 'required|string',
            'booksNum' => 'required|string',
        ]);
        //dd($validated);

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

            dd($book);
        }
    }
}
