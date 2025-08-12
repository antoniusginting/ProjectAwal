<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'plat_polisi',
        'nama_supir',
        'nama_barang',
        'bruto',
        'tara',
        'netto',
        'keterangan',
        'jam_masuk',
        'jam_keluar',
        'nama_lumbung',
        'status_transfer',
        'silo',
        'user_id',
        'laporan_lumbung_keluar_id',
        'laporan_lumbung_masuk_id',
        'penjualan_id',

    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
     // Relasi penjualan
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(penjualan::class, 'penjualan_id');
    }


    // Relasi ke lumbung asal (keluar)
    public function laporanLumbungKeluar(): BelongsTo
    {
        return $this->belongsTo(LaporanLumbung::class, 'laporan_lumbung_keluar_id');
    }

    // Relasi ke lumbung tujuan (masuk)
    public function laporanLumbungMasuk(): BelongsTo
    {
        return $this->belongsTo(LaporanLumbung::class, 'laporan_lumbung_masuk_id');
    }
}
