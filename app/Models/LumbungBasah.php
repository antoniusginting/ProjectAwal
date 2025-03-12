<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LumbungBasah extends Model
{
    protected $fillable = [
        'no_lumbung_basah',
        'jenis_jagung',
        'id_sortiran_1',
        'id_sortiran_2',
        'id_sortiran_3',
        'id_sortiran_4',
        'id_sortiran_5',
        'total_netto',
        'status',
    ];

    // Relasi ke Kapasitas
    public function kapasitaslumbungbasah()
    {
        return $this->belongsTo(KapasitasLumbungBasah::class, 'no_lumbung_basah', 'id');
    }

    public function sortiran1()
    {
        return $this->belongsTo(Sortiran::class, 'id_sortiran_1');
    }

    public function sortiran2()
    {
        return $this->belongsTo(Sortiran::class, 'id_sortiran_2');
    }

    public function sortiran3()
    {
        return $this->belongsTo(Sortiran::class, 'id_sortiran_3');
    }

    public function sortiran4()
    {
        return $this->belongsTo(Sortiran::class, 'id_sortiran_4');
    }

    public function sortiran5()
    {
        return $this->belongsTo(Sortiran::class, 'id_sortiran_5');
    }

    public function sortiran6()
    {
        return $this->belongsTo(Sortiran::class, 'id_sortiran_6');
    }
    // Pengurangan kapasitas_sisa dengan total_netto
    protected static function booted()
    {
        static::creating(function ($lumbungBasah) {
            // Cari data kapasitas berdasarkan ID
            $kapasitas = KapasitasLumbungBasah::find($lumbungBasah->no_lumbung_basah);

            if ($kapasitas) {
                // Pastikan kapasitas cukup sebelum dikurangi
                if ($kapasitas->kapasitas_sisa >= $lumbungBasah->total_netto) {
                    $kapasitas->decrement('kapasitas_sisa', $lumbungBasah->total_netto);
                } else {
                    throw new \Exception('Kapasitas tidak mencukupi!');
                }
            }
        });

        static::updating(function ($lumbungBasah) {
            // Ambil data lama sebelum perubahan
            $oldLumbungBasah = $lumbungBasah->getOriginal();
            $oldNetto = $oldLumbungBasah['total_netto'] ?? 0;
            $oldNoLumbung = $oldLumbungBasah['no_lumbung_basah'];
        
            // Cek apakah nomor lumbung berubah
            if ($oldNoLumbung !== $lumbungBasah->no_lumbung_basah) {
                throw new \Exception('Nomor Lumbung tidak dapat diubah!');
            }
        
            // Cari data kapasitas berdasarkan ID lama
            $kapasitas = KapasitasLumbungBasah::find($oldNoLumbung);
        
            if ($kapasitas) {
                // Hitung selisih perubahan
                $selisih = $lumbungBasah->total_netto - $oldNetto;
        
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
