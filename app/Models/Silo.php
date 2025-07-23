<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
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
