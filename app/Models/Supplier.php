<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'nama_supplier',
        'jenis_supplier',
        'no_ktp',
        'npwp',
        'no_rek',
        'nama_bank',
        'atas_nama_bank',
    ];
}
