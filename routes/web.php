<?php

use App\Http\Controllers\SortiranController;
use App\Http\Controllers\SuratJalanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

//Print Sortiran
Route::get('/print-sortiran/{id}',[SortiranController::class,'printSortiranRecord'])->name('PRINT.SORTIRAN');

//Print SuratJalan
Route::get('/print-suratjalan/{id}',[SuratJalanController::class,'printSuratJalanRecord'])->name('PRINT.SURATJALAN');