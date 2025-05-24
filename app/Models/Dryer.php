<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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


    public function sortirans(): BelongsToMany
    {
        return $this->belongsToMany(Sortiran::class, 'dryer_has_sortiran')
            ->withTimestamps();
    }
    public function timbanganTrontons(): BelongsToMany
    {
        return $this->belongsToMany(TimbanganTronton::class, 'dryers_has_timbangan_trontons')
            ->withTimestamps();
    }
    public function laporanLumbungs(): BelongsToMany
    {
        return $this->belongsToMany(LaporanLumbung::class, 'laporan_lumbung_has_dryers')
            ->withTimestamps();
    }
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

    protected static function booted()
    {
        // Event saat data dibuat
        static::creating(function ($dryer) {
            $kapasitas = KapasitasDryer::find($dryer->id_kapasitas_dryer);

            if (!$kapasitas) {
                throw ValidationException::withMessages([
                    'id_kapasitas_dryer' => 'Kapasitas Dryer tidak ditemukan.',
                ]);
            }

            if ($kapasitas->kapasitas_sisa >= $dryer->total_netto) {
                $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto);
            } else {
                Notification::make()
                    ->danger()
                    ->title('Kapasitas Tidak Mencukupi')
                    ->body('Total netto yang diinput melebihi kapasitas sisa Dryer.')
                    ->persistent()
                    ->send();

                throw ValidationException::withMessages([
                    'total_netto' => 'Total netto melebihi kapasitas sisa Dryer.',
                ]);
            }
        });

        // Event saat data diperbarui
        static::updating(function ($dryer) {
            DB::transaction(function () use ($dryer) {
                $olddryer = $dryer->getOriginal();
                $oldNetto = $olddryer['total_netto'] ?? 0;
                $oldNoLumbung = $olddryer['id_kapasitas_dryer'];

                if ($oldNoLumbung !== $dryer->id_kapasitas_dryer) {
                    throw ValidationException::withMessages([
                        'id_kapasitas_dryer' => 'Nomor Lumbung tidak dapat diubah!',
                    ]);
                }

                $kapasitas = KapasitasDryer::find($oldNoLumbung);

                if (!$kapasitas) {
                    throw ValidationException::withMessages([
                        'id_kapasitas_dryer' => 'Kapasitas Dryer tidak ditemukan.',
                    ]);
                }

                // Kembalikan kapasitas lama
                $kapasitas->increment('kapasitas_sisa', $oldNetto);

                // Periksa apakah kapasitas cukup untuk nilai baru
                if ($kapasitas->kapasitas_sisa >= $dryer->total_netto) {
                    $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto);
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Kapasitas Tidak Mencukupi')
                        ->body('Total netto yang diinput melebihi kapasitas sisa Dryer.')
                        ->persistent()
                        ->send();

                    throw ValidationException::withMessages([
                        'total_netto' => 'Total netto melebihi kapasitas sisa Dryer.',
                    ]);
                }
            });
        });

        // Event saat data dihapus
        static::deleting(function ($dryer) {
            $kapasitas = KapasitasDryer::find($dryer->id_kapasitas_dryer);

            if ($kapasitas) {
                $kapasitas->increment('kapasitas_sisa', $dryer->total_netto);
            }
        });
    }
}
