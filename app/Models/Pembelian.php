<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $fillable = [
        // 'id_mobil', // Foreing key ke mobils
        'plat_polisi',
        'id_supplier', // Forein key ke suppliers
        'nama_supir',
        'nama_barang',
        'no_container',
        'brondolan',
        'bruto',
        'tara',
        'netto',
        'keterangan',
        'no_po',
        'jam_masuk',
        'jam_keluar',
        'user_id'
    ];
    
    // Relasi ke User
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    // Relasi ke Mobil
    public function mobil()
    {
        return $this->belongsTo(Mobil::class, 'id_mobil', 'id');
    }

     // Relasi ke tabel supplier
     public function supplier()
     {
         return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
     }

}
