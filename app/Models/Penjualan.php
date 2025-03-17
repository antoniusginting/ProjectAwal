<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $fillable = [
        'id_mobil', // Foreing key ke mobils
        'id_supplier', // Forein key ke suppliers
        'nama_supir',
        'nama_barang',
        'brondolan',
        'bruto',
        'tara',
        'netto',
        'keterangan',
        'jam_masuk',
        'jam_keluar',
        'no_lumbung',
        'nama_lumbung',
    ];

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
