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
    ];

    public function penjualanLuar()
    {
        return $this->hasMany(PenjualanAntarPulau::class);
    }
}
