<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbanganTronton extends Model
{
    protected $fillable = [
        'id_timbangan_jual_1',
        'id_timbangan_jual_2',
        'id_timbangan_jual_3',
        'id_timbangan_jual_4',
        'id_timbangan_jual_5',
        'id_timbangan_jual_6',
        'total_bruto',
        'tambah_berat',
        'bruto_final',
        'keterangan',
    ];

    public function penjualan1()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_1','id');
    }

    public function penjualan2()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_2');
    }

    public function penjualan3()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_3');
    }

    public function penjualan4()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_4');
    }

    public function penjualan5()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_5');
    }

    public function penjualan6()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_6');
    }

}
