<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sortiran extends Model
{
    protected $casts = [
        'foto_jagung_1' => 'array',
        'foto_jagung_2' => 'array',
        'foto_jagung_3' => 'array',
        'foto_jagung_4' => 'array',
        'foto_jagung_5' => 'array',
        'foto_jagung_6' => 'array',
    ];
    protected $fillable = [
        'id_pembelian',
        'no_lumbung',
        'berat_tungkul',
        'netto_bersih',
        'kualitas_jagung_1',
        'foto_jagung_1',
        'x1_x10_1',
        'jumlah_karung_1',
        'tonase_1',
        'kualitas_jagung_2',
        'foto_jagung_2',
        'x1_x10_2',
        'jumlah_karung_2',
        'tonase_2',
        'kualitas_jagung_3',
        'foto_jagung_3',
        'x1_x10_3',
        'jumlah_karung_3',
        'tonase_3',
        'kualitas_jagung_4',
        'foto_jagung_4',
        'x1_x10_4',
        'jumlah_karung_4',
        'tonase_4',
        'kualitas_jagung_5',
        'foto_jagung_5',
        'x1_x10_5',
        'jumlah_karung_5',
        'tonase_5',
        'kualitas_jagung_6',
        'foto_jagung_6',
        'x1_x10_6',
        'jumlah_karung_6',
        'tonase_6',
        'kadar_air',
        'total_karung',
        'user_id',
        'keterangan',
        'cek',
    ];
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian', 'id');
    }

    // // Relasi ke tabel pembelian
    // public function kapasitas()
    // {
    //     return $this->belongsTo(KapasitasLumbungBasah::class, 'id_lumbung_basah', 'id');
    // }

    public function lumbungBasahs(): BelongsToMany
    {
        return $this->belongsToMany(LumbungBasah::class, 'lumbung_basah_has_sortiran')
            ->withTimestamps();
    }
}
