<?php

namespace App\Services;

use App\Models\Dryer;
use App\Models\KapasitasDryer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class DryerService
{
    /**
     * Buat dryer baru
     */
    public function create(array $data): Dryer
    {
        return DB::transaction(function () use ($data) {
            $totalNetto = $this->parseTotalNetto($data['total_netto'] ?? '');

            // Validasi kapasitas dryer
            $kapasitas = $this->getKapasitasDryer($data['id_kapasitas_dryer']);
            $this->validateKapasitasAda($kapasitas, $data['id_kapasitas_dryer']);

            // Validasi kapasitas mencukupi
            if ($kapasitas->kapasitas_sisa < $totalNetto) {
                $this->throwKapasitasNotification();
                throw ValidationException::withMessages([
                    'total_netto' => 'Total netto melebihi kapasitas sisa dryer.',
                ]);
            }

            // Kurangi kapasitas dryer
            $kapasitas->decrement('kapasitas_sisa', $totalNetto);

            // Buat dryer
            $dryer = Dryer::create($data);

            return $dryer;
        });
    }

    /**
     * Update data dryer dengan perhitungan ulang kapasitas
     */
    public function update(Dryer $dryer, array $data): Dryer
    {
        return DB::transaction(function () use ($dryer, $data) {
            // 1. Ambil nilai baru & lama - FIXED: Added null coalescing operator
            $newTotal = $this->parseTotalNetto($data['total_netto'] ?? '');
            $oldTotal = $dryer->total_netto_integer;
            
            $oldKapasitasId = $dryer->id_kapasitas_dryer;
            $newKapasitasId = $data['id_kapasitas_dryer'] ?? $oldKapasitasId;

            // 2. Jika id_kapasitas_dryer berubah (pindah dryer)
            if ((int)$newKapasitasId !== $oldKapasitasId) {
                // Kembalikan kapasitas penuh ke dryer lama
                $oldKapasitas = $this->getKapasitasDryer($oldKapasitasId);
                if ($oldKapasitas) {
                    $oldKapasitas->increment('kapasitas_sisa', $oldTotal);
                }

                // Ambil kapasitas dryer baru dan validasi
                $newKapasitas = $this->getKapasitasDryer($newKapasitasId);
                $this->validateKapasitasAda($newKapasitas, $newKapasitasId);

                // Validasi apakah dryer baru bisa menampung total netto
                if ($newKapasitas->kapasitas_sisa < $newTotal) {
                    // Rollback perubahan ke dryer lama
                    $oldKapasitas->decrement('kapasitas_sisa', $oldTotal);
                    
                    $this->throwKapasitasNotification();
                    throw ValidationException::withMessages([
                        'id_kapasitas_dryer' => 'Dryer tujuan tidak memiliki kapasitas yang cukup.',
                        'total_netto' => 'Total netto melebihi kapasitas sisa dryer tujuan.',
                    ]);
                }

                // Kurangi kapasitas dryer baru
                $newKapasitas->decrement('kapasitas_sisa', $newTotal);
                
                $kapasitas = $newKapasitas;
            }
            // 3. Jika dryer tidak berubah tapi total berubah
            else {
                $kapasitas = $this->getKapasitasDryer($dryer->id_kapasitas_dryer);
                $this->validateKapasitasAda($kapasitas, $dryer->id_kapasitas_dryer);

                if ($newTotal !== $oldTotal) {
                    $delta = $newTotal - $oldTotal;
                    if ($delta > 0 && $kapasitas->kapasitas_sisa < $delta) {
                        $this->throwKapasitasNotification();
                        throw ValidationException::withMessages([
                            'total_netto' => 'Total netto melebihi kapasitas sisa dryer.',
                        ]);
                    }

                    // decrement jika bertambah, increment jika berkurang
                    $method = $delta > 0 ? 'decrement' : 'increment';
                    $kapasitas->{$method}('kapasitas_sisa', abs($delta));
                }
            }

            // 4. Simpan perubahan pada dryer
            $dryer->update($data);

            return $dryer->fresh();
        });
    }

    /**
     * Hapus dryer
     */
    public function delete(Dryer $dryer): bool
    {
        return DB::transaction(function () use ($dryer) {
            // Kembalikan kapasitas ke dryer
            $kapasitas = $this->getKapasitasDryer($dryer->id_kapasitas_dryer);
            if ($kapasitas) {
                $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
            }

            return $dryer->delete();
        });
    }

    // Helper methods
    private function parseTotalNetto(?string $totalNetto): int
    {
        // Handle null or empty string
        if (empty($totalNetto)) {
            return 0;
        }

        return (int) str_replace('.', '', $totalNetto);
    }

    private function getKapasitasDryer(int $idKapasitas): ?KapasitasDryer
    {
        return KapasitasDryer::find($idKapasitas);
    }

    private function validateKapasitasAda(?KapasitasDryer $kapasitas, int $idKapasitas): void
    {
        if (!$kapasitas) {
            throw ValidationException::withMessages([
                'id_kapasitas_dryer' => 'Kapasitas Dryer tidak ditemukan.',
            ]);
        }
    }

    private function throwKapasitasNotification(): void
    {
        Notification::make()
            ->danger()
            ->title('Kapasitas Tidak Mencukupi')
            ->body('Total netto yang diinput melebihi kapasitas sisa dryer.')
            ->persistent()
            ->send();
    }
}