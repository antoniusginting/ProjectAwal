<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanAntarPulau extends Model
{
    protected $fillable = [
        'kode_segel',
        'nama_barang',
        'netto',
        'no_container',
        'user_id',
        'status',
        'netto_diterima',
        'kapasitas_kontrak_jual_id',
        'pembelian_antar_pulau_id',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->status === 'TERIMA') {
                $model->netto_diterima = $model->netto;
            } elseif ($model->status !== 'SETENGAH') {
                $model->netto_diterima = 0;
            }
        });
    }

    public function kapasitasKontrakJual()
    {
        return $this->belongsTo(KapasitasKontrakJual::class, 'kapasitas_kontrak_jual_id');
    }

    public function pembelianAntarPulau()
    {
        return $this->belongsTo(PembelianAntarPulau::class, 'pembelian_antar_pulau_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
