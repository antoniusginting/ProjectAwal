<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KendaraanMuat extends Model
{
    protected $casts = [
        'foto' => 'array',
    ];
    protected $fillable = [
        'nama_supir',
        'kendaraan_id',
        'tonase',
        'foto',
        'tujuan',
        'user_id',
        'jam_masuk',
        'jam_keluar',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function kendaraan(){
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }
}
