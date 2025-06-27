<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Подключаем класс irbis64
require_once app_path('Http/Controllers/irbis_class.php');


class SearchReaderController extends Controller
{
    public function searchReader(Request $request)
    {
        $reader = "";
        $irbisServerPort = config('app.irbisServerPort');
        
        $validated = $request->validate([
            'reader' => 'required|string',
        ]);
        $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', 'RDR');
        if ($irbis->login()) {
            $readerRec = $irbis->records_search('RI='.$validated['reader'], 10, 1);
            if(isset($readerRec['records'][0][1])){
                session(['reader' => $readerRec['records'][0][1]]);
                $reader = $readerRec['records'][0][1];
            }else{
                session(['reader' => "читатель: не найден"]);
                $reader = "читатель: не найден";
            }
        }

    //return view('search', compact('result', 'complectRecs', 'invNum'));
    return view('search', compact('reader'));
    }
}
