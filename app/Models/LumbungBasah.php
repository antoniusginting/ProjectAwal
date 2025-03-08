<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LumbungBasah extends Model
{
    protected $guarded = [
        'jenis',
        'no_lumbung',
        'no_lumbung_basah',
        

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
