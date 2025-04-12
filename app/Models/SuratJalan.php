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
        'satuan_muatan',
        'user_id',
        'bruto_final',
        'netto_final',
        'tambah_berat',
    ];

    // Relasi ke User
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
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
