<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sortiran extends Model
{
    protected $fillable = [];

    // Relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id', 'id');
    }
}
