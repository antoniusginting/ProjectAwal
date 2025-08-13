<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Silo extends Model
{
    protected $fillable = [
        'stok',
        'nama',
        'status',
    ];


    public function laporanLumbungs()
    {
        return $this->hasMany(LaporanLumbung::class);
    }
    // public function luars()
    // {
    //     return $this->hasMany(Luar::class);
    // }
    // public function stockLuar()
    // {
    //     return $this->hasMany(StockLuar::class);
    // }
    public function langsir()
    {
        return $this->hasMany(Transfer::class);
    }
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
    }
    // Transfer yang keluar dari lumbung ini (lumbung sebagai asal)
    public function transferKeluar(): HasMany
    {
        return $this->hasMany(Transfer::class, 'silo_keluar_id');
    }

    // Transfer yang masuk ke lumbung ini (lumbung sebagai tujuan)
    public function transferMasuk(): HasMany
    {
        return $this->hasMany(Transfer::class, 'silo_masuk_id');
    }
    // // Method untuk menghitung total stok (stok awal + semua penambahan)
    // public function getTotalStockAttribute()
    // {
    //     return $this->stok + $this->stockAdditions()->sum('quantity');
    // }

    // public function timbanganTrontons(): BelongsToMany
    // {
    //     return $this->belongsToMany(TimbanganTronton::class, 'silos_has_timbangan_trontons')
    //         ->withTimestamps();
    // }

    // public function laporanLumbungs(): BelongsToMany
    // {
    //     return $this->belongsToMany(LaporanLumbung::class, 'silos_has_laporan_lumbungs')
    //         ->withTimestamps();
    // }
}
