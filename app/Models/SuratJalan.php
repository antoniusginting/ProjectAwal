<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $fillable = [
        'id_kontrak',
        'id_kontrak2',
        'id_alamat',
        'id_timbangan_tronton',
        'kota',
        'po',
        'netto',
    ];

    // Relasi ke Kontrak
    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id');
    }
    // Relasi ke Kontrak2
    public function kontrak2()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak2', 'id');
    }
    // Relasi ke Alamat
    public function alamat()
    {
        return $this->belongsTo(AlamatKontrak::class, 'id_alamat', 'id');
    }
    // Relasi ke Timbangan Tronton
    public function tronton()
    {
        return $this->belongsTo(TimbanganTronton::class, 'id_timbangan_tronton', 'id');
    }
}
