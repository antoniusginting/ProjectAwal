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
            $totalNetto = $this->parseTotalNetto($data['total_netto']);

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
            // 1. Ambil nilai baru & lama
            $newTotal = $this->parseTotalNetto($data['total_netto']);
            $oldTotal = $dryer->total_netto_integer; // atau $dryer->getOriginal('total_netto_integer')

            // 2. Kalau id_kapasitas_dryer juga berubah, rollback dulu di kapasitas lama
            if (
                isset($data['id_kapasitas_dryer'])
                && (int)$data['id_kapasitas_dryer'] !== $dryer->id_kapasitas_dryer
            ) {
                // kembalikan semua netto ke kapasitas lama
                $oldK = $this->getKapasitasDryer($dryer->id_kapasitas_dryer);
                $oldK->increment('kapasitas_sisa', $oldTotal);
                // ambil kapasitas baru
                $newK = $this->getKapasitasDryer($data['id_kapasitas_dryer']);
                $this->validateKapasitasAda($newK, $data['id_kapasitas_dryer']);
                // pakai newK untuk decrement nanti
                $kapasitas = $newK;
            } else {
                $kapasitas = $this->getKapasitasDryer($dryer->id_kapasitas_dryer);
                $this->validateKapasitasAda($kapasitas, $dryer->id_kapasitas_dryer);
            }

            // 3. Kalau total berubah, hitung selisih dan adjust
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

            // 4. Simpan perubahan pada dryer
            $dryer->update($data);

            return $dryer->fresh();
        });
    }

    /**
     * Update dryer status
     */
    public function updateStatusToCompleted(array $newDryerIds, array $oldDryerIds = []): void
    {
        DB::transaction(function () use ($newDryerIds, $oldDryerIds) {
            // Dryer yang baru dipilih (selesai) - kembalikan kapasitas
            $newlySelected = array_diff($newDryerIds, $oldDryerIds);
            if (!empty($newlySelected)) {
                $dryers = Dryer::whereIn('id', $newlySelected)->get();
                foreach ($dryers as $dryer) {
                    // Kembalikan kapasitas karena dryer selesai
                    $kapasitas = $this->getKapasitasDryer($dryer->id_kapasitas_dryer);
                    if ($kapasitas) {
                        $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
                    }
                    $dryer->update(['status' => 'completed']);
                }
            }

            // Dryer yang di-deselect (kembali proses) - potong kapasitas lagi
            $deselected = array_diff($oldDryerIds, $newDryerIds);
            if (!empty($deselected)) {
                $dryers = Dryer::whereIn('id', $deselected)->get();
                foreach ($dryers as $dryer) {
                    // Potong kapasitas lagi karena dryer kembali ke status proses
                    $kapasitas = $this->getKapasitasDryer($dryer->id_kapasitas_dryer);
                    if ($kapasitas) {
                        $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                    }
                    $dryer->update(['status' => 'processing']); // atau status default lainnya
                }
            }
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
    private function parseTotalNetto(string $totalNetto): int
    {
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
