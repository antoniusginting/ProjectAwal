<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanLumbung extends Model
{
    protected $fillable = [
        "user_id",
        "lumbung",
        "berat_dryer",
        "berat_penjualan",
        "hasil",
        "status_silo",
        "berat_langsir",
        "status",
    ];
    public function timbanganTrontons(): BelongsToMany
    {
        return $this->belongsToMany(TimbanganTronton::class, 'laporan_lb_has_timbangant')
            ->withTimestamps();
    }

    // Untuk penjualan masuk
    public function penjualanMasuk(): BelongsToMany
    {
        return $this->belongsToMany(Penjualan::class, 'laporan_lumbungs_has_penjualans')
            ->withPivot('tipe_penjualan')
            ->wherePivot('tipe_penjualan', 'masuk')
            ->withTimestamps();
    }

    // Untuk penjualan keluar
    public function penjualanKeluar(): BelongsToMany
    {
        return $this->belongsToMany(Penjualan::class, 'laporan_lumbungs_has_penjualans')
            ->withPivot('tipe_penjualan')
            ->wherePivot('tipe_penjualan', 'keluar')
            ->withTimestamps();
    }

    // Tetap bisa pakai yang general
    public function penjualans(): BelongsToMany
    {
        return $this->belongsToMany(Penjualan::class, 'laporan_lumbungs_has_penjualans')
            ->withPivot('tipe_penjualan')
            ->withTimestamps();
    }


    // public function penjualans(): BelongsToMany
    // {
    //     return $this->belongsToMany(Penjualan::class, 'laporan_lumbungs_has_penjualans')
    //         ->withTimestamps();
    // }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function dryers(): BelongsToMany
    {
        return $this->belongsToMany(Dryer::class, 'laporan_lumbung_has_dryers', 'laporan_lumbung_id', 'dryer_id')
            ->withTimestamps();
    }

    // // Property untuk menyimpan dryer IDs sebelum update
    // protected $originalDryerIds = null;

    // protected static function booted()
    // {
    //     // Event sebelum update - simpan data lama
    //     static::updating(function ($laporanLumbung) {
    //         // Simpan dryer IDs yang lama sebelum update
    //         $laporanLumbung->originalDryerIds = $laporanLumbung->dryers()->pluck('dryers.id')->toArray();
    //     });

    //     // Event setelah model dan relasi disimpan
    //     static::saved(function ($laporanLumbung) {
    //         // Untuk create, tidak perlu rollback
    //         if ($laporanLumbung->wasRecentlyCreated) {
    //             return;
    //         }
    //     });

    //     // Event ketika record dihapus
    //     static::deleting(function ($laporanLumbung) {
    //         static::rollbackKapasitasDryer($laporanLumbung);
    //     });
    // }

    // // Method yang dipanggil dari Resource setelah relasi disimpan
    // public function updateKapasitasDryerAfterSync($newDryerIds = null)
    // {
    //     // Jika tidak ada dryer IDs baru yang diberikan, ambil dari relasi
    //     if ($newDryerIds === null) {
    //         $newDryerIds = $this->dryers()->pluck('dryers.id')->toArray();
    //     }

    //     // Untuk create, langsung tambahkan kapasitas
    //     if ($this->wasRecentlyCreated) {
    //         $this->updateNewDryers($newDryerIds);
    //         return;
    //     }

    //     // Untuk update, hanya rollback dan update yang berbeda
    //     if ($this->originalDryerIds !== null) {
    //         $oldDryerIds = $this->originalDryerIds;

    //         // Dryer yang dihapus (ada di lama, tidak ada di baru)
    //         $removedDryerIds = array_diff($oldDryerIds, $newDryerIds);

    //         // Dryer yang ditambah (ada di baru, tidak ada di lama)
    //         $addedDryerIds = array_diff($newDryerIds, $oldDryerIds);

    //         // Rollback hanya dryer yang dihapus
    //         if (!empty($removedDryerIds)) {
    //             $this->rollbackOldDryers($removedDryerIds);
    //         }

    //         // Update hanya dryer yang ditambah
    //         if (!empty($addedDryerIds)) {
    //             $this->updateNewDryers($addedDryerIds);
    //         }
    //     }

    //     // Reset original dryer IDs
    //     $this->originalDryerIds = null;
    // }

    // protected function rollbackOldDryers($oldDryerIds)
    // {
    //     if (empty($oldDryerIds)) return;

    //     $oldDryers = \App\Models\Dryer::whereIn('id', $oldDryerIds)->get();

    //     foreach ($oldDryers as $oldDryer) {
    //         $oldKapasitas = \App\Models\KapasitasDryer::find($oldDryer->id_kapasitas_dryer);
    //         if ($oldKapasitas) {
    //             $oldNettoValue = (int) preg_replace('/[^0-9]/', '', $oldDryer->total_netto);
    //             $oldKapasitas->decrement('kapasitas_sisa', $oldNettoValue);
    //         }
    //     }
    // }

    // protected function updateNewDryers($newDryerIds)
    // {
    //     if (empty($newDryerIds)) return;

    //     $selectedDryers = \App\Models\Dryer::whereIn('id', $newDryerIds)->get();

    //     foreach ($selectedDryers as $dryer) {
    //         $kapasitas = \App\Models\KapasitasDryer::find($dryer->id_kapasitas_dryer);
    //         if ($kapasitas) {
    //             $nettoValue = (int) preg_replace('/[^0-9]/', '', $dryer->total_netto);
    //             $kapasitas->increment('kapasitas_sisa', $nettoValue);
    //         }
    //     }
    // }

    // protected static function rollbackKapasitasDryer($laporanLumbung)
    // {
    //     $dryerIds = $laporanLumbung->dryers()->pluck('dryers.id')->toArray();
    //     if (empty($dryerIds)) return;

    //     $dryers = \App\Models\Dryer::whereIn('id', $dryerIds)->get();

    //     foreach ($dryers as $dryer) {
    //         $kapasitas = \App\Models\KapasitasDryer::find($dryer->id_kapasitas_dryer);
    //         if ($kapasitas) {
    //             $nettoValue = (int) preg_replace('/[^0-9]/', '', $dryer->total_netto);
    //             $kapasitas->decrement('kapasitas_sisa', $nettoValue);
    //         }
    //     }
    // }
}
