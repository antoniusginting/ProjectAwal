<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LumbungBasah extends Model
{
    protected $fillable = [
        'no_lumbung_basah',
        'tujuan',
        'total_netto',
        'status',
        'user_id',
    ];
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Relasi ke Kapasitas
    public function kapasitaslumbungbasah()
    {
        return $this->belongsTo(KapasitasLumbungBasah::class, 'no_lumbung_basah', 'id');
    }

    // public function sortiran1()
    // {
    //     return $this->belongsTo(Sortiran::class, 'id_sortiran_1');
    // }

    // public function sortiran2()
    // {
    //     return $this->belongsTo(Sortiran::class, 'id_sortiran_2');
    // }

    // public function sortiran3()
    // {
    //     return $this->belongsTo(Sortiran::class, 'id_sortiran_3');
    // }

    // public function sortiran4()
    // {
    //     return $this->belongsTo(Sortiran::class, 'id_sortiran_4');
    // }

    // public function sortiran5()
    // {
    //     return $this->belongsTo(Sortiran::class, 'id_sortiran_5');
    // }

    // public function sortiran6()
    // {
    //     return $this->belongsTo(Sortiran::class, 'id_sortiran_6');
    // }

    public function sortirans(): BelongsToMany
    {
        return $this->belongsToMany(Sortiran::class, 'lumbung_basah_has_sortiran')
            ->withTimestamps();
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
                    Notification::make()
                        ->danger()
                        ->title('Kapasitas Tidak Mencukupi')
                        ->body('Total netto yang diinput melebihi kapasitas sisa lumbung basah.')
                        ->persistent()
                        ->send();
                    // Gunakan ValidationException untuk tetap di halaman form
                    throw ValidationException::withMessages([
                        'total_netto' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                    ]);
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
                        Notification::make()
                            ->danger()
                            ->title('Kapasitas Tidak Mencukupi')
                            ->body('Total netto yang diinput melebihi kapasitas sisa lumbung basah.')
                            ->persistent()
                            ->send();
                        // Gunakan ValidationException untuk tetap di halaman form
                        throw ValidationException::withMessages([
                            'total_netto' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                        ]);
                    }
                } elseif ($selisih < 0) {
                    // Jika netto berkurang, tambahkan kembali ke kapasitas
                    $kapasitas->increment('kapasitas_sisa', abs($selisih));
                }
            }
        });
    }
}
