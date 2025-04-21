<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KendaraanMasuks extends Model
{
    protected $fillable = [
        'status',
        'nama_sup_per',
        'plat_polisi',
        'nama_barang',
        'keterangan',
        'jam_masuk',
        'jam_keluar',
        'user_id',
        'nomor_antrian',
        'status_selesai',
        'status_awal',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
