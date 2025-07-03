<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'laporan_lumbung_id',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke laporan lumbung
    public function laporanLumbung()
    {
        return $this->belongsTo(LaporanLumbung::class, 'laporan_lumbung_id');
    }
}
