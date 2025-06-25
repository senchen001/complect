<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        dd($validated);
    }
}
