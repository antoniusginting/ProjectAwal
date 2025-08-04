<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dryer extends Model
{
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PENDING = 'pending';
    protected $fillable = [
        'id_kapasitas_dryer',
        'operator',
        'nama_barang',
        'rencana_kadar',
        'hasil_kadar',
        'total_netto',
        'pj',
        'status',
        'no_cc',
        'user_id',
        'laporan_lumbung_id',
    ];

    // Relasi ke laporan lumbung
    public function laporanLumbung()
    {
        return $this->belongsTo(LaporanLumbung::class, 'laporan_lumbung_id');
    }

    public function sortir()
    {
        return $this->belongsTo(Sortiran::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function sortirans(): BelongsToMany
    {
        return $this->belongsToMany(Sortiran::class, 'dryer_has_sortiran')
            ->withTimestamps();
    }
    public function timbanganTrontons(): BelongsToMany
    {
        return $this->belongsToMany(TimbanganTronton::class, 'dryers_has_timbangan_trontons')
            ->withTimestamps();
    }
    // public function laporanLumbungs(): BelongsToMany
    // {
    //     return $this->belongsToMany(LaporanLumbung::class, 'laporan_lumbung_has_dryers')
    //         ->withTimestamps();
    // }
    // Relasi ke Kapasitas
    public function kapasitasdryer()
    {
        return $this->belongsTo(KapasitasDryer::class, 'id_kapasitas_dryer', 'id');
    }

    public function getTotalNettoIntegerAttribute(): int
    {
        return (int) str_replace('.', '', $this->total_netto);
    }


    protected static function booted()
    {
        static::saved(function (Dryer $dryer) {

            if ($dryer->status === 'completed') {
                foreach ($dryer->sortirans as $sortiran) {
                    $sortiran->update(['status' => 'completed']);
                }
            }


            if ($dryer->status === 'pending') {
                foreach ($dryer->sortirans as $sortiran) {
                    $sortiran->update(['status' => 'pending']);
                }
            }


            if ($dryer->status === 'processing') {
                foreach ($dryer->sortirans as $sortiran) {
                    $sortiran->update(['status' => 'processing']);
                }
            }
        });
    }

    // Di Model Dryer.php
    // public function getHasilPenguranganNumericFinal()
    // {
    //     $laporanLumbung = $this->laporanLumbungs->first();

    //     if (!$laporanLumbung) {
    //         return 0;
    //     }

    //     $lumbungTujuan = $laporanLumbung->lumbung ?? null;
    //     $nilai_dryers_sum_total_netto = $laporanLumbung->dryers->sum('total_netto');

    //     // Hitung total keseluruhan filtered
    //     $totalKeseluruhanFiltered = 0;
    //     foreach ($laporanLumbung->timbangantrontons as $timbanganTronton) {
    //         $allPenjualan = collect();
    //         $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];

    //         foreach ($relasiPenjualan as $relasi) {
    //             if (isset($timbanganTronton->$relasi)) {
    //                 $dataRelasi = $timbanganTronton->$relasi;
    //                 if ($dataRelasi instanceof \Illuminate\Database\Eloquent\Collection) {
    //                     $allPenjualan = $allPenjualan->merge($dataRelasi);
    //                 } elseif ($dataRelasi !== null) {
    //                     $allPenjualan->push($dataRelasi);
    //                 }
    //             }
    //         }

    //         $filteredPenjualan = $allPenjualan->where('nama_lumbung', $lumbungTujuan);
    //         $totalKeseluruhanFiltered += $filteredPenjualan->sum('netto');
    //     }

    //     $totalNettoPenjualansBaru = $laporanLumbung->penjualans->sum('netto') ?? 0;
    //     $totalGabungan = $totalKeseluruhanFiltered + $totalNettoPenjualansBaru;

    //     // Hitung hasil_pengurangan_numeric_final
    //     if ($nilai_dryers_sum_total_netto > 0) {
    //         return ($totalGabungan / $nilai_dryers_sum_total_netto) * 100;
    //     }

    //     return 0;
    // }
}
