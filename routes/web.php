<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\makeComplectController;

Route::get('/', function () {
    return view('search');
});

Route::post('/search', [SearchController::class, 'search'])->name('search');

Route::get('/makeComplect', [makeComplectController::class, 'show'])->name('makeComplect');