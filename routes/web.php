<?php

use App\Http\Controllers\SortiranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});


Route::get('/print-sortiran/{id}',[SortiranController::class,'printSortiranRecord'])->name('PRINT.SORTIRAN');