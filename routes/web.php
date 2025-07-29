<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
Route::post('/createNewComplect', [makeComplectController::class, 'createNewComplect'])->name('createNewComplect');
Route::post('/closeComplect', [makeComplectController::class, 'closeComplect'])->name('closeComplect');
Route::delete('/removeFromComplect', [makeComplectController::class, 'remove'])->name('removeFromComplect');

Route::post('/giveComplect', [giveComplectController::class, 'giveComplect'])->name('giveComplect');

Route::get('/inventory', [InventoryController::class, 'show'])->name('inventory');
Route::post('/invApprove', [InventoryController::class, 'invApprove'])->name('invApprove');
Route::post('/invFind', [InventoryController::class, 'invFind'])->name('invFind');
Route::get('/invFind', function() {
    return redirect()->route('inventory');
})->name('invFind.get');
Route::post('/approveAccepted', [InventoryController::class, 'approveAccepted'])->name('approveAccepted');
Route::post('/rastshifr/store', [InventoryController::class, 'storeRastshifr'])->name('rastshifr.store');
Route::post('/storloc/store', [InventoryController::class, 'storeStorloc'])->name('storloc.store');

Auth::routes();
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
