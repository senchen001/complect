<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile'); // Убедитесь, что у вас есть представление profile.blade.php
    }
}
