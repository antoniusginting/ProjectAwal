<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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

    protected static function booted()
    {
        // Event saat data dibuat
        static::creating(function ($sortiran) {
            // Konversi netto_bersih dari varchar ke integer (hapus titik pemisah ribuan)
            $nettoBersih = (int) str_replace('.', '', $sortiran->netto_bersih);

            // Cari data kapasitas berdasarkan ID
            $kapasitas = KapasitasLumbungBasah::find($sortiran->no_lumbung_basah);

            if (!$kapasitas) {
                throw ValidationException::withMessages([
                    'no_lumbung_basah' => 'Kapasitas Lumbung Basah tidak ditemukan.',
                ]);
            }

            if ($kapasitas->kapasitas_sisa >= $nettoBersih) {
                $kapasitas->decrement('kapasitas_sisa', $nettoBersih);
            } else {
                Notification::make()
                    ->danger()
                    ->title('Kapasitas Tidak Mencukupi')
                    ->body('Total netto yang diinput melebihi kapasitas sisa lumbung basah.')
                    ->persistent()
                    ->send();

                throw ValidationException::withMessages([
                    'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                ]);
            }
        });

        // Event saat data diperbarui
        static::updating(function ($sortiran) {
            DB::transaction(function () use ($sortiran) {
                $oldSortiran = $sortiran->getOriginal();
                $oldNetto = (int) str_replace('.', '', $oldSortiran['netto_bersih'] ?? '0');
                $oldNoLumbung = $oldSortiran['no_lumbung_basah'];
                $oldStatus = $oldSortiran['status'] ?? false; // Status lama
                $newStatus = $sortiran->status; // Status baru

                $newNetto = (int) str_replace('.', '', $sortiran->netto_bersih);

                // Jika hanya status yang berubah (toggle diklik)
                if (
                    $oldSortiran['netto_bersih'] === $sortiran->netto_bersih &&
                    $oldNoLumbung === $sortiran->no_lumbung_basah &&
                    $oldStatus !== $newStatus
                ) {

                    $kapasitas = KapasitasLumbungBasah::find($sortiran->no_lumbung_basah);

                    if ($kapasitas) {
                        if ($newStatus) {
                            // Jika status berubah menjadi true (toggle ON), kembalikan kapasitas
                            $kapasitas->increment('kapasitas_sisa', $oldNetto);
                        } else {
                            // Jika status berubah menjadi false (toggle OFF), kurangi kapasitas
                            if ($kapasitas->kapasitas_sisa >= $oldNetto) {
                                $kapasitas->decrement('kapasitas_sisa', $oldNetto);
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Kapasitas Tidak Mencukupi')
                                    ->body('Kapasitas sisa tidak mencukupi untuk mengaktifkan kembali sortiran.')
                                    ->persistent()
                                    ->send();

                                throw ValidationException::withMessages([
                                    'status' => 'Kapasitas sisa tidak mencukupi untuk mengaktifkan kembali sortiran.',
                                ]);
                            }
                        }
                    }
                    return; // Keluar dari function karena hanya status yang berubah
                }

                // Logic untuk update netto_bersih (seperti sebelumnya)
                if ($oldNoLumbung !== $sortiran->no_lumbung_basah) {
                    throw ValidationException::withMessages([
                        'no_lumbung_basah' => 'Nomor Lumbung tidak dapat diubah!',
                    ]);
                }

                $kapasitas = KapasitasLumbungBasah::find($oldNoLumbung);

                if (!$kapasitas) {
                    throw ValidationException::withMessages([
                        'no_lumbung_basah' => 'Kapasitas Lumbung Basah tidak ditemukan.',
                    ]);
                }

                // Hanya update kapasitas jika status tidak aktif (false)
                if (!$newStatus) {
                    // Kembalikan kapasitas lama
                    $kapasitas->increment('kapasitas_sisa', $oldNetto);

                    // Periksa apakah kapasitas cukup untuk nilai baru
                    if ($kapasitas->kapasitas_sisa >= $newNetto) {
                        $kapasitas->decrement('kapasitas_sisa', $newNetto);
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Kapasitas Tidak Mencukupi')
                            ->body('Total netto yang diinput melebihi kapasitas sisa lumbung basah.')
                            ->persistent()
                            ->send();

                        throw ValidationException::withMessages([
                            'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                        ]);
                    }
                }
            });
        });

        // Event saat data dihapus
        static::deleting(function ($sortiran) {
            $kapasitas = KapasitasLumbungBasah::find($sortiran->no_lumbung_basah);

            if ($kapasitas) {
                // Hanya kembalikan kapasitas jika status tidak aktif (false)
                if (!$sortiran->status) {
                    $kapasitas->increment('kapasitas_sisa', (int) str_replace('.', '', $sortiran->netto_bersih));
                }
            }
        });
    }
}
