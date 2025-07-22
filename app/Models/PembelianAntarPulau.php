<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianAntarPulau extends Model
{
    protected $fillable = [
        'kode_segel',
        'nama_barang',
        'netto',
        'no_container',
        'user_id',
        'nama_ekspedisi',
        "kapasitas_kontrak_beli_id",
    ];


    // Relasi ke luar kapasitas kontrak jual
    public function kapasitasKontrakBeli()
    {
        return $this->belongsTo(KapasitasKontrakBeli::class, 'kapasitas_kontrak_beli_id');
    }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
