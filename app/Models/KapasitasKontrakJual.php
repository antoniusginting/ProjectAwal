<?php

namespace App\Models;

use App\Models\PenjualanAntarPulau;
use Illuminate\Database\Eloquent\Model;

class KapasitasKontrakJual extends Model
{
    protected $fillable = [
        'stok',
        'nama',
        'status',
        'harga',
    ];

    public function penjualanLuar()
    {
        return $this->hasMany(PenjualanAntarPulau::class);
    }
    public function suratJalan()
    {
        return $this->hasMany(SuratJalan::class);
    }
}
