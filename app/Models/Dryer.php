<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dryer extends Model
{
    protected $fillable = [
        'id_kapasitas_dryer',
        'id_lumbung_1',
        'id_lumbung_2',
        'id_lumbung_3',
        'id_lumbung_4',
        'operator',
        'nama_barang',
        'lumbung_tujuan',
        'rencana_kadar',
        'hasil_kadar',
        'total_netto',
        'pj',
    ];

    // Relasi ke Kapasitas
    public function kapasitasdryer()
    {
        return $this->belongsTo(KapasitasDryer::class, 'id_kapasitas_dryer', 'id');
    }

    public function lumbung1()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_1');
    }

    public function lumbung2()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_2');
    }
    
    public function lumbung3()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_3');
    }

    public function lumbung4()
    {
        return $this->belongsTo(LumbungBasah::class, 'id_lumbung_4');
    }

    // Pengurangan kapasitas_sisa dengan total_netto
    protected static function booted()
    {
        static::creating(function ($dryer) {
            // Cari data kapasitas berdasarkan ID
            $kapasitas = KapasitasDryer::find($dryer->id_kapasitas_dryer);

            if ($kapasitas) {
                // Pastikan kapasitas cukup sebelum dikurangi
                if ($kapasitas->kapasitas_sisa >= $dryer->total_netto) {
                    $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto);
                } else {
                    throw new \Exception('Kapasitas tidak mencukupi!');
                }
            }
        });

        static::updating(function ($dryer) {
            // Ambil data lama sebelum perubahan
            $olddryer = $dryer->getOriginal();
            $oldNetto = $olddryer['total_netto'] ?? 0;
            $oldNoLumbung = $olddryer['id_kapasitas_dryer'];

            // Cek apakah nomor lumbung berubah
            if ($oldNoLumbung !== $dryer->id_kapasitas_dryer) {
                throw new \Exception('Nomor Lumbung tidak dapat diubah!');
            }

            // Cari data kapasitas berdasarkan ID lama
            $kapasitas = KapasitasDryer::find($oldNoLumbung);

            if ($kapasitas) {
                // Hitung selisih perubahan
                $selisih = $dryer->total_netto - $oldNetto;

                if ($selisih > 0) {
                    // Jika netto bertambah, pastikan kapasitas cukup
                    if ($kapasitas->kapasitas_sisa >= $selisih) {
                        $kapasitas->decrement('kapasitas_sisa', $selisih);
                    } else {
                        throw new \Exception('Kapasitas tidak mencukupi!');
                    }
                } elseif ($selisih < 0) {
                    // Jika netto berkurang, tambahkan kembali ke kapasitas
                    $kapasitas->increment('kapasitas_sisa', abs($selisih));
                }
            }
        });
    }
}
