<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchReaderController;
use App\Http\Controllers\makeComplectController;

Route::get('/', function () {
    return view('search');
});

Route::post('/search', [SearchController::class, 'search'])->name('search');
Route::post('/searchReader', [SearchReaderController::class, 'searchReader'])->name('searchReader');

Route::get('/makeComplect', [makeComplectController::class, 'show'])->name('makeComplect');
Route::post('/store', [makeComplectController::class, 'store'])->name('store');