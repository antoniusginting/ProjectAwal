<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlamatKontrak extends Model
{
    protected $fillable = [
        'id_kontrak',
        'alamat',
    ];

    // Relasi ke Kontrak
    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id');
    }
}
