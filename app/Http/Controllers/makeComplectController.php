<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class makeComplectController extends Controller
{
    public function Show(){
        return view('makeComplect.index');
    }
}
