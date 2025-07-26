<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $fillable = [
        'id_kontrak',
        'id_alamat',
        'id_timbangan_tronton',
        'kota',
        'po',
        'user_id',
        'bruto_final',
        'netto_final',
        'tambah_berat',
        'jenis_mobil',
        'status',
        'netto_diterima',
        'kapasitas_kontrak_jual_id',
    ];


    // Relasi ke luar kapasitas kontrak jual
    public function kapasitasKontrakJual()
    {
        return $this->belongsTo(KapasitasKontrakJual::class, 'kapasitas_kontrak_jual_id');
    }
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Relasi ke Kontrak
    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id');
    }
    // Relasi ke Kontrak2
    public function kontrak2()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak2', 'id');
    }
    // Relasi ke Alamat
    public function alamat()
    {
        return $this->belongsTo(AlamatKontrak::class, 'id_alamat', 'id');
    }
    // Relasi ke Timbangan Tronton
    public function tronton()
    {
        return $this->belongsTo(TimbanganTronton::class, 'id_timbangan_tronton', 'id');
    }

    public function timbanganTronton()
    {
        return $this->belongsTo(TimbanganTronton::class, 'id_timbangan_tronton');
    }
    // Method untuk mengambil BK (plat_polisi) yang sudah digunakan
    public static function getUsedBK()
    {
        return self::with('timbanganTronton')
            ->get()
            ->map(function ($surat) {
                // Pastikan data timbanganTronton ada dan plat_polisi tidak null
                return $surat->timbanganTronton ? $surat->timbanganTronton->plat_polisi : null;
            })
            ->filter()   // Hapus nilai null
            ->unique()
            ->values()
            ->all();
    }
}
