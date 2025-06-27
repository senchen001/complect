<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

require_once app_path('Http/Controllers/irbis_class.php');

class makeComplectController extends Controller
{
    public function Show(){
        return view('makeComplect.index');
    }

    public function Store(Request $request){
         $irbisServerPort = config('app.irbisServerPort');

        $validated = $request->validate([
            'complID' => 'required|string',
            'invnum' => 'required|string',
        ]);

        $irbis = new \irbis64('127.0.0.1', $irbisServerPort, '1', '1', 'RDRKV2');
        if ($irbis->login()) {
            //найдем запись комплекта с идентификатором complID
           $complRec = $irbis->records_search('I='.$validated['complID'], 10, 1, $format = '@all');
           //$complRec = $irbis->record_read(2);
           
           $mfn = $complRec['records'][0][0];
           $field_num = 1033;
           $invNumToRec = $validated['invnum'];
           $record = $irbis->record_read($mfn);
            //dd($record);
           if(is_object($record)){
                $record->addField($invNumToRec, $field_num);
                $write_result = $irbis->record_write($record->getRecordArray(), true, true);
                //dd($write_result);
                if ($write_result !== '') {
                    dd('Ошибка записи: ' . $irbis->error($write_result));
                }
                $irbis->logout();
           }else{
            dd("не удалось получить запись по mfn");
           }
        }

        return view('makeComplect.store');
    }
}
