<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuarPulau extends Model
{
    protected $fillable = [
        'stok',
        'nama',
        'status',
        'harga',
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
