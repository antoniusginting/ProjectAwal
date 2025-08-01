<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KendaraanMasuks extends Model
{
    protected $casts = [
        'foto' => 'array',
    ];
    protected $fillable = [
        'status',
        'nama_sup_per',
        'plat_polisi',
        'nama_barang',
        'keterangan',
        'jam_masuk',
        'foto',
        'jam_keluar',
        'user_id',
        'nomor_antrian',
        'status_selesai',
        'status_awal',
        'jenis',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
