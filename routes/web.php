<?php

use App\Filament\Resources\PenjualanResource;
use App\Http\Controllers\DryerController;
use App\Http\Controllers\LaporanLumbungController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
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

//Print Laporan LaporanPenjualan
Route::get('/print-laporanpenjualan/{id}',[LaporanPenjualanController::class,'printLaporanPenjualanRecord'])->name('PRINT.LAPORANPENJUALAN');

//Print Timbangan Penjualan
Route::get('/print-penjualan/{id}',[PenjualanController::class,'printPenjualanRecord'])->name('PRINT.PENJUALAN');

//Print Timbangan Pembelian
Route::get('/print-pembelian/{id}',[PembelianController::class,'printPembelianRecord'])->name('PRINT.PEMBELIAN');

//Print Timbangan dryer
Route::get('/print-dryer/{id}',[DryerController::class,'printDryerRecord'])->name('PRINT.DRYER');

//Print Timbangan Laporan Lumbung
Route::get('/print-laporanlumbung/{id}',[LaporanLumbungController::class,'printLaporanLumbungRecord'])->name('PRINT.LAPORANLUMBUNG');

// //Print Silo
// Route::get('/print-silo/{id}',[SiloController::class,'printLaporanLumbungRecord'])->name('PRINT.LAPORANLUMBUNG');