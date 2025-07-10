<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Luar extends Model
{
    protected $fillable = [
        'kode_segel',
        'nama_barang',
        'netto',
        'no_container',
        'user_id',
        'nama_ekspedisi',
        "silo_id",
    ];


    // Relasi ke silo
    public function silos()
    {
        return $this->belongsTo(Silo::class, 'silo_id');
    }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function timbanganTrontons()
    {
        return $this->hasMany(TimbanganTronton::class, 'id_luar_1', 'id');
    }
}
