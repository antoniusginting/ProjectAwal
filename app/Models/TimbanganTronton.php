<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimbanganTronton extends Model
{
    protected $casts = [
        'foto' => 'array',
    ];
    protected $fillable = [
        'id_timbangan_jual_1',
        'id_timbangan_jual_2',
        'id_timbangan_jual_3',
        'id_timbangan_jual_4',
        'id_timbangan_jual_5',
        'id_timbangan_jual_6',
        'bruto_akhir',
        'total_netto',
        'tara_awal',
        'keterangan',
        'user_id',
        'status',
        'foto',
        'id_penjualan_antar_pulau_1',
        'id_penjualan_antar_pulau_2',
        'id_penjualan_antar_pulau_3',
    ];

    public function dryers(): BelongsToMany
    {
        return $this->belongsToMany(Dryer::class, 'dryers_has_timbangan_trontons')
            ->withTimestamps();
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function penjualan1()
    {
        return $this->belongsTo(Penjualan::class, 'id_timbangan_jual_1', 'id');
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

    public function penjualanAntarPulau1()
    {
        return $this->belongsTo(PenjualanAntarPulau::class, 'id_penjualan_antar_pulau_1', 'id');
    }

    public function penjualanAntarPulau2()
    {
        return $this->belongsTo(PenjualanAntarPulau::class, 'id_penjualan_antar_pulau_2', 'id');
    }

    public function penjualanAntarPulau3()
    {
        return $this->belongsTo(PenjualanAntarPulau::class, 'id_penjualan_antar_pulau_3', 'id');
    }
    public function suratJalans()
    {
        // Ganti second argument kalau nama FK-mu bukan timbangan_tronton_id
        return $this->hasMany(SuratJalan::class, 'id_timbangan_tronton');
    }
}
