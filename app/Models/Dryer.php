<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dryer extends Model
{
    protected $fillable = [
        'id_kapasitas_dryer',
        'id_lumbung_1',
        'id_lumbung_2',
        'operator',
        'jenis_jagung',
        'lumbung_tujuan',
        'rencana_kadar',
        'hasil_kadar',
        'total_netto',
    ];

    // Relasi ke Kapasitas
    public function kapasitasdryer()
    {
        return $this->belongsTo(KapasitasDryer::class, 'id_kapasitas_dryer', 'id');
    }

    public function lumbung1()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_1');
    }

    public function lumbung2()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_2');
    }
    
}
