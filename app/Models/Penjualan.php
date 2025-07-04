<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Penjualan extends Model
{
    protected $fillable = [
        //'id_mobil', // Foreing key ke mobils
        'no_container',
        'plat_polisi',
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
        'status_timbangan',
        'jumlah_karung',
        'silo',
        'user_id',
        'laporan_lumbung_id',
        'silo_id',
    ];

    // Relasi ke User
    public function user()
    {
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
    // public function laporanLumbungs(): BelongsToMany
    // {
    //     return $this->belongsToMany(LaporanLumbung::class, 'laporan_lumbungs_has_penjualans')
    //         ->withTimestamps();
    // }

    // Relasi ke laporan lumbung
    public function laporanLumbung()
    {
        return $this->belongsTo(LaporanLumbung::class, 'laporan_lumbung_id');
    }
    // Relasi ke silo
    public function silo()
    {
        return $this->belongsTo(Silo::class, 'silo_id');
    }
}
