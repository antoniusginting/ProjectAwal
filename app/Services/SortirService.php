<?php

namespace App\Services;

use App\Models\Sortiran;
use App\Models\KapasitasLumbungBasah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class SortirService
{
    /**
     * Buat sortiran baru
     */
    public function create(array $data): Sortiran
    {
        return DB::transaction(function () use ($data) {
            $nettoBersih = $this->parseNettoBersih($data['netto_bersih'] ?? '');

            // Validasi kapasitas lumbung - FIXED: Added null coalescing
            $noLumbungBasah = $data['no_lumbung_basah'] ?? null;
            if (!$noLumbungBasah) {
                throw ValidationException::withMessages([
                    'no_lumbung_basah' => 'No lumbung basah harus diisi.',
                ]);
            }

            $kapasitas = $this->getKapasitasLumbung($noLumbungBasah);
            $this->validateKapasitasAda($kapasitas, $noLumbungBasah);

            // Validasi kapasitas mencukupi
            if ($kapasitas->kapasitas_sisa < $nettoBersih) {
                $this->throwKapasitasNotification();
                throw ValidationException::withMessages([
                    'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                ]);
            }

            // Kurangi kapasitas lumbung
            $kapasitas->decrement('kapasitas_sisa', $nettoBersih);

            // Buat sortiran
            $sortiran = Sortiran::create($data);

            return $sortiran;
        });
    }

    /**
     * Update sortiran
     */
    public function update(Sortiran $sortiran, array $data): Sortiran
    {
        return DB::transaction(function () use ($sortiran, $data) {
            // FIXED: Merge dengan data existing untuk field yang mungkin disabled
            $data = array_merge([
                'no_lumbung_basah' => $sortiran->no_lumbung_basah, // Fallback ke nilai lama
                'netto_bersih' => $sortiran->getOriginal('netto_bersih'), // Fallback untuk netto_bersih juga
            ], $data);

            $newNettoBersih = $this->parseNettoBersih($data['netto_bersih'] ?? '');
            $oldNettoBersih = $sortiran->netto_bersih_integer;
            $oldNoLumbung = $sortiran->no_lumbung_basah;
            $newNoLumbung = $data['no_lumbung_basah'];

            // Jika nomor lumbung berubah
            if ($oldNoLumbung != $newNoLumbung) {
                // Kembalikan kapasitas ke lumbung lama
                $oldKapasitas = $this->getKapasitasLumbung($oldNoLumbung);
                if ($oldKapasitas) {
                    $oldKapasitas->increment('kapasitas_sisa', $oldNettoBersih);
                }

                // Validasi dan kurangi kapasitas lumbung baru
                $newKapasitas = $this->getKapasitasLumbung($newNoLumbung);
                $this->validateKapasitasAda($newKapasitas, $newNoLumbung);

                // Validasi kapasitas mencukupi
                if ($newKapasitas->kapasitas_sisa < $newNettoBersih) {
                    $this->throwKapasitasNotification();
                    throw ValidationException::withMessages([
                        'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                    ]);
                }

                // Kurangi kapasitas lumbung baru
                $newKapasitas->decrement('kapasitas_sisa', $newNettoBersih);
            }
            // Jika nomor lumbung sama tapi netto berubah
            else if ($oldNettoBersih != $newNettoBersih) {
                $kapasitas = $this->getKapasitasLumbung($oldNoLumbung);
                $this->validateKapasitasAda($kapasitas, $oldNoLumbung);

                // Hitung selisih netto
                $selisihNetto = $newNettoBersih - $oldNettoBersih;

                // Jika netto bertambah, validasi kapasitas
                if ($selisihNetto > 0) {
                    if ($kapasitas->kapasitas_sisa < $selisihNetto) {
                        $this->throwKapasitasNotification();
                        throw ValidationException::withMessages([
                            'netto_bersih' => 'Total netto melebihi kapasitas sisa lumbung basah.',
                        ]);
                    }
                    // Kurangi kapasitas sesuai selisih
                    $kapasitas->decrement('kapasitas_sisa', $selisihNetto);
                }
                // Jika netto berkurang, kembalikan selisih ke kapasitas
                else if ($selisihNetto < 0) {
                    $kapasitas->increment('kapasitas_sisa', abs($selisihNetto));
                }
            }

            // Update data sortiran
            $sortiran->update($data);

            return $sortiran->fresh();
        });
    }

    // Di SortirService
    public function updateStatusToDryer(array $newSortirIds, array $oldSortirIds = []): void
    {
        DB::transaction(function () use ($newSortirIds, $oldSortirIds) {
            // Sortiran yang baru dipilih (masuk dryer) - kembalikan kapasitas
            $newlySelected = array_diff($newSortirIds, $oldSortirIds);
            if (!empty($newlySelected)) {
                $sortirans = Sortiran::whereIn('id', $newlySelected)->get();
                foreach ($sortirans as $sortiran) {
                    // Kembalikan kapasitas karena sortiran masuk ke dryer
                    $kapasitas = $this->getKapasitasLumbung($sortiran->no_lumbung_basah);
                    if ($kapasitas) {
                        $kapasitas->increment('kapasitas_sisa', $sortiran->netto_bersih_integer);
                    }
                    $sortiran->update(['status' => 'in_dryer']);
                }
            }

            // Sortiran yang di-deselect (keluar dari dryer) - potong kapasitas lagi
            $deselected = array_diff($oldSortirIds, $newSortirIds);
            if (!empty($deselected)) {
                $sortirans = Sortiran::whereIn('id', $deselected)->get();
                foreach ($sortirans as $sortiran) {
                    // Potong kapasitas lagi karena sortiran kembali ke status awal
                    $kapasitas = $this->getKapasitasLumbung($sortiran->no_lumbung_basah);
                    if ($kapasitas) {
                        $kapasitas->decrement('kapasitas_sisa', $sortiran->netto_bersih_integer);
                    }
                    $sortiran->update(['status' => 'available']); // atau status default lainnya
                }
            }
        });
    }

    /**
     * Hapus sortiran
     */
    public function delete(Sortiran $sortiran): bool
    {
        return DB::transaction(function () use ($sortiran) {
            // Kembalikan kapasitas ke lumbung
            $kapasitas = $this->getKapasitasLumbung($sortiran->no_lumbung_basah);
            if ($kapasitas) {
                $kapasitas->increment('kapasitas_sisa', $sortiran->netto_bersih_integer);
            }

            return $sortiran->delete();
        });
    }

    // Helper methods
    private function parseNettoBersih(?string $nettoBersih): int
    {
        // FIXED: Handle null values
        if (empty($nettoBersih)) {
            return 0;
        }

        return (int) str_replace('.', '', $nettoBersih);
    }

    private function getKapasitasLumbung(int $noLumbung): ?KapasitasLumbungBasah
    {
        return KapasitasLumbungBasah::find($noLumbung);
    }

    private function validateKapasitasAda(?KapasitasLumbungBasah $kapasitas, int $noLumbung): void
    {
        if (!$kapasitas) {
            throw ValidationException::withMessages([
                'no_lumbung_basah' => 'Kapasitas Lumbung Basah tidak ditemukan.',
            ]);
        }
    }

    private function throwKapasitasNotification(): void
    {
        Notification::make()
            ->danger()
            ->title('Kapasitas Tidak Mencukupi')
            ->body('Total netto yang diinput melebihi kapasitas sisa lumbung basah.')
            ->persistent()
            ->send();
    }
}
