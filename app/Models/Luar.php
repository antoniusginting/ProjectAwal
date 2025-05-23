<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Luar extends Model
{
    protected $fillable = [
        'kode_segel',
        'nama_barang',
        'id_supplier', // Forein key ke suppliers
        'netto',
        'no_container',
        'user_id',
        'nama_ekspedisi',
    ];
    
    // Relasi ke User
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

     // Relasi ke tabel supplier
     public function supplier()
     {
         return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
     }
}
