<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sortiran extends Model
{
    protected $fillable = [
        'pembelian_id',
        'lumbung',
        'kualitas_jagung_1',
        'foto_jagung_1',
        'x1_x10_1',
        'jumlah_karung_1',
        'kadar_air_1',
        'kualitas_jagung_2',
        'foto_jagung_2',
        'x1_x10_2',
        'jumlah_karung_2',
        'kadar_air_2',
        'kualitas_jagung_3',
        'foto_jagung_3',
        'x1_x10_3',
        'jumlah_karung_3',
        'kadar_air_3',
    ];

    // Relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id', 'id');
    }
}
