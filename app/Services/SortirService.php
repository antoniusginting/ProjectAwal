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
            $nettoBersih = $this->parseNettoBersih($data['netto_bersih']);

            // Validasi kapasitas lumbung
            $kapasitas = $this->getKapasitasLumbung($data['no_lumbung_basah']);
            $this->validateKapasitasAda($kapasitas, $data['no_lumbung_basah']);

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
    private function parseNettoBersih(string $nettoBersih): int
    {
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
