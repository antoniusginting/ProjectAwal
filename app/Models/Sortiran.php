<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sortiran extends Model
{
    protected $fillable = [
        'id_pembelian',
        'id_lumbung_basah',
        'kualitas_jagung_1',
        'foto_jagung_1',
        'x1_x10_1',
        'jumlah_karung_1',
        'kualitas_jagung_2',
        'foto_jagung_2',
        'x1_x10_2',
        'jumlah_karung_2',
        'kualitas_jagung_3',
        'foto_jagung_3',
        'x1_x10_3',
        'jumlah_karung_3',
        'kualitas_jagung_4',
        'foto_jagung_4',
        'x1_x10_4',
        'jumlah_karung_4',
        'kualitas_jagung_5',
        'foto_jagung_5',
        'x1_x10_5',
        'jumlah_karung_5',
        'kualitas_jagung_6',
        'foto_jagung_6',
        'x1_x10_6',
        'jumlah_karung_6',
        'kadar_air',
        'total_karung',
    ];

    // Relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian', 'id');
    }

    // Relasi ke tabel pembelian
    public function kapasitas()
    {
        return $this->belongsTo(KapasitasLumbungBasah::class, 'id_lumbung_basah', 'id');
    }
}
