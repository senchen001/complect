<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SearchReaderController;
use App\Http\Controllers\makeComplectController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\giveComplectController;

Route::get('/', function () {
    return view('search');
});

Route::post('/search', [SearchController::class, 'search'])->name('search');
Route::post('/searchReader', [SearchReaderController::class, 'searchReader'])->name('searchReader');

Route::get('/makeComplect', [makeComplectController::class, 'show'])->name('makeComplect');
Route::post('/store', [makeComplectController::class, 'store'])->name('store');

Route::get('/giveComplect', [giveComplectController::class, 'giveComplect'])->name('giveComplect');

Route::get('/inventory', [InventoryController::class, 'show'])->name('inventory');
Route::post('/invApprove', [InventoryController::class, 'invApprove'])->name('invApprove');
Route::post('/invFind', [InventoryController::class, 'invFind'])->name('invFind');
Route::post('/approveAccepted', [InventoryController::class, 'approveAccepted'])->name('approveAccepted');
//Route::get('/approveSuccess', [InventoryController::class, 'approveSuccess']);

Auth::routes();
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
