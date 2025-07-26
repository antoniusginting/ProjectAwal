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
        'jumlah_setengah',
        'netto_diterima',
        'kapasitas_kontrak_jual_id',
        'pembelian_antar_pulau_id', // Pastikan ini ada
    ];

    public function kapasitasKontrakJual()
    {
        return $this->belongsTo(KapasitasKontrakJual::class, 'kapasitas_kontrak_jual_id');
    }

    // Relasi yang benar ke PembelianAntarPulau
    public function pembelianAntarPulau()
    {
        return $this->belongsTo(PembelianAntarPulau::class, 'pembelian_antar_pulau_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
