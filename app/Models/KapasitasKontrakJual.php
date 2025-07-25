<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KapasitasKontrakJual extends Model
{
    protected $fillable = [
        'stok',
        'no_po',
        'nama',
        'status',
        'harga',
        'no_po',
    ];

    public function penjualanLuar()
    {
        return $this->hasMany(PenjualanAntarPulau::class, 'kapasitas_kontrak_jual_id');
    }

    public function suratJalan()
    {
        return $this->hasMany(SuratJalan::class);
    }

    // ====> Tambahkan 3 accessor helper berikut
    public function getTotalKeluarAttribute(): int
    {
        return (int) $this->penjualanLuar()
            ->whereIn('status', ['TERIMA', 'SETENGAH'])
            ->sum(DB::raw('COALESCE(netto_diterima, netto)'));
    }

    public function getTotalMasukKembaliAttribute(): int
    {
        return (int) $this->penjualanLuar()
            ->whereIn('status', ['RETUR', 'TOLAK'])
            ->sum(DB::raw('COALESCE(netto_diterima, netto)'));
    }

    public function getStokSisaAttribute(): int
    {
        return (int) (($this->stok ?? 0) - $this->total_keluar + $this->total_masuk_kembali);
    }
}
