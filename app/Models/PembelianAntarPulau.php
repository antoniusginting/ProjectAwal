<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianAntarPulau extends Model
{
    protected $fillable = [
        'kode_segel',
        'nama_barang',
        'netto',
        'no_container',
        'user_id',
        'nama_ekspedisi',
        "luar_pulau_id",
    ];


    // Relasi ke luar pulau
    public function luarPulau()
    {
        return $this->belongsTo(LuarPulau::class, 'luar_pulau_id');
    }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
