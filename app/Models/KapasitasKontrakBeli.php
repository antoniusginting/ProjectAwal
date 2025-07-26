<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KapasitasKontrakBeli extends Model
{
    protected $fillable = [
        'stok',
        'nama',
        'status',
        'harga',
        'supplier',
    ];

    public function pembelianLuar()
    {
        return $this->hasMany(PembelianAntarPulau::class);
    }
    // public function penjualanLuar()
    // {
    //     return $this->hasMany(PenjualanAntarPulau::class);
    // }
}
