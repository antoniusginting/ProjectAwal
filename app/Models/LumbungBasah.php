<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LumbungBasah extends Model
{
    protected $fillable = [
        'no_lumbung_basah',
        'jenis_jagung',
        'id_sortiran_1',
        'id_sortiran_2',
        'id_sortiran_3',
        'id_sortiran_4',
        'id_sortiran_5',
        'total_netto',
        'status',
        

    ];

     // Relasi ke Kapasitas
     public function kapasitaslumbungbasah()
     {
         return $this->belongsTo(KapasitasLumbungBasah::class, 'no_lumbung_basah', 'id');
     }
     
     // Relasi ke Sortiran
     public function sortiran()
     {
         return $this->belongsTo(Sortiran::class, 'id_sortiran_1', 'id');
     }
}
