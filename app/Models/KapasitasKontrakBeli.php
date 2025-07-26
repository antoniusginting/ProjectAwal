<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KapasitasKontrakBeli extends Model
{
    protected $fillable = [
        'kategori',
        'nama',
        'stok',
        'harga',
        'status'
    ];

    public function pembelianLuar()
    {
        return $this->hasMany(PembelianAntarPulau::class);
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'kontrak_id');
    }
    // public function penjualanLuar()
    // {
    //     return $this->hasMany(PenjualanAntarPulau::class);
    // }
}
