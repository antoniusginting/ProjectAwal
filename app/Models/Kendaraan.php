<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $fillable = [
        'plat_polisi_terbaru',
        'plat_polisi_sebelumnya',
        'pemilik',
        'nama_supir',
        'nama_kernek',
        'jenis_mobil',
        'status_sp',
    ];
}
