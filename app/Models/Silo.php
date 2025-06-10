<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Silo extends Model
{
    protected $fillable = [
        'stok',
        'nama',
    ];

    public function timbanganTrontons(): BelongsToMany
    {
        return $this->belongsToMany(TimbanganTronton::class, 'silos_has_timbangan_trontons')
            ->withTimestamps();
    }

    public function laporanLumbungs(): BelongsToMany
    {
        return $this->belongsToMany(LaporanLumbung::class, 'silos_has_laporan_lumbungs')
            ->withTimestamps();
    }
}
