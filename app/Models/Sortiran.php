<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sortiran extends Model
{
    protected $casts = [
        'foto_jagung_1' => 'array',
        'foto_jagung_2' => 'array',
        'foto_jagung_3' => 'array',
        'foto_jagung_4' => 'array',
        'foto_jagung_5' => 'array',
        'foto_jagung_6' => 'array',
    ];
    protected $fillable = [
        'id_pembelian',
        'no_lumbung',
        'berat_tungkul',
        'netto_bersih',
        'kualitas_jagung_1',
        'foto_jagung_1',
        'x1_x10_1',
        'jumlah_karung_1',
        'tonase_1',
        'kualitas_jagung_2',
        'foto_jagung_2',
        'x1_x10_2',
        'jumlah_karung_2',
        'tonase_2',
        'kualitas_jagung_3',
        'foto_jagung_3',
        'x1_x10_3',
        'jumlah_karung_3',
        'tonase_3',
        'kualitas_jagung_4',
        'foto_jagung_4',
        'x1_x10_4',
        'jumlah_karung_4',
        'tonase_4',
        'kualitas_jagung_5',
        'foto_jagung_5',
        'x1_x10_5',
        'jumlah_karung_5',
        'tonase_5',
        'kualitas_jagung_6',
        'foto_jagung_6',
        'x1_x10_6',
        'jumlah_karung_6',
        'tonase_6',
        'kadar_air',
        'total_karung',
        'user_id',
        'keterangan',
        'cek',
        'no_lumbung_basah',
    ];
    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian', 'id');
    }

    // // Relasi ke tabel pembelian
    // public function kapasitas()
    // {
    //     return $this->belongsTo(KapasitasLumbungBasah::class, 'id_lumbung_basah', 'id');
    // }

    // Relasi ke Kapasitas
    public function kapasitaslumbungbasah()
    {
        return $this->belongsTo(KapasitasLumbungBasah::class, 'no_lumbung_basah', 'id');
    }

    // Pengurangan kapasitas_sisa dengan netto_bersih
    protected static function booted()
    {
        static::creating(function ($sortiran) {
            // Konversi netto_bersih dari varchar ke integer (hapus titik pemisah ribuan)
            $nettoBersih = (int) str_replace('.', '', $sortiran->netto_bersih);

            // Cari data kapasitas berdasarkan ID
            $kapasitas = KapasitasLumbungBasah::find($sortiran->no_lumbung_basah);

            if ($kapasitas) {
                // Pastikan kapasitas cukup sebelum dikurangi
                if ($kapasitas->kapasitas_sisa >= $nettoBersih) {
                    $kapasitas->decrement('kapasitas_sisa', $nettoBersih);
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Kapasitas Tidak Mencukupi')
                        ->body('Total netto yang diinput melebihi kapasitas sisa lumbung basah.')
                        ->persistent()
                        ->send();
                    // Gunakan ValidationException untuk tetap di halaman form
                    throw ValidationException::withMessages([
                        'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                    ]);
                }
            }
        });

        static::updating(function ($sortiran) {
            // Ambil data lama sebelum perubahan
            $oldSortiran = $sortiran->getOriginal();
            $oldNetto = (int) str_replace('.', '', $oldSortiran['netto_bersih'] ?? '0'); // Konversi ke integer dengan hapus titik
            $oldNoLumbung = $oldSortiran['no_lumbung_basah'];

            // Konversi netto_bersih baru dari varchar ke integer (hapus titik pemisah ribuan)
            $newNetto = (int) str_replace('.', '', $sortiran->netto_bersih);

            // Cek apakah nomor lumbung berubah
            if ($oldNoLumbung !== $sortiran->no_lumbung_basah) {
                throw new \Exception('Nomor Lumbung tidak dapat diubah!');
            }

            // Cari data kapasitas berdasarkan ID lama
            $kapasitas = KapasitasLumbungBasah::find($oldNoLumbung);

            if ($kapasitas) {
                // Hitung selisih perubahan dengan nilai yang sudah dikonversi
                $selisih = $newNetto - $oldNetto;

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
                            'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
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
