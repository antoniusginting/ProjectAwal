<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanAntarPulau extends Model
{
    protected $fillable = [
        'kode_segel',
        'nama_barang',
        'netto',
        'no_container',
        'user_id',
        'status',
        'netto_diterima',
        // "luar_pulau_id",
        "kapasitas_kontrak_jual_id",
    ];


    // Relasi ke luar kapasitas kontrak jual
    public function kapasitasKontrakJual()
    {
        return $this->belongsTo(KapasitasKontrakJual::class, 'kapasitas_kontrak_jual_id');
    }
    // Relasi ke luar pulau
    public function luarPulau()
    {
        return $this->belongsTo(LuarPulau::class, 'luar_pulau_id');
    }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
