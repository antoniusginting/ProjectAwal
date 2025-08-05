<?php

namespace App\Observers;

use App\Models\LaporanLumbung;
use App\Models\KapasitasDryer;

class LaporanLumbungObserver
{
    public function updated(LaporanLumbung $laporanLumbung)
    {
        // Cek apakah field 'lumbung' berubah
        if ($laporanLumbung->isDirty('lumbung')) {
            $this->handleLumbungChange($laporanLumbung);
        }
    }

    private function handleLumbungChange(LaporanLumbung $laporanLumbung): void
    {
        $oldLumbung = $laporanLumbung->getOriginal('lumbung');
        $newLumbung = $laporanLumbung->lumbung;

        // Update semua dryer yang menggunakan laporan lumbung ini
        $dryers = $laporanLumbung->dryers; // Asumsi ada relasi dryers()

        foreach ($dryers as $dryer) {
            $kapasitas = KapasitasDryer::find($dryer->id_kapasitas_dryer);

            if (!$kapasitas) {
                continue;
            }

            $oldStatus = $dryer->status;
            $sortirIds = $dryer->sortirans->pluck('id')->toArray();

            // Dari lumbung kosong ke ada isi
            if (empty(trim($oldLumbung)) && !empty(trim($newLumbung))) {
                // pending → completed
                if ($oldStatus === 'pending') {
                    $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
                    $dryer->update(['status' => 'completed']);

                    // Sortiran selesai, status jadi completed dan kembalikan kapasitas lumbung basah
                    $this->updateSortirStatus($sortirIds, 'completed', true);
                }
            }

            // Dari lumbung ada isi ke kosong
            elseif (!empty(trim($oldLumbung)) && empty(trim($newLumbung))) {
                // completed → pending
                if ($oldStatus === 'completed') {
                    $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                    $dryer->update(['status' => 'pending']);

                    // Sortiran kembali ke in_dryer dan potong kapasitas lumbung basah
                    $this->updateSortirStatus($sortirIds, 'in_dryer', false, true);
                }
            }
        }
    }

    /**
     * Update status sortiran dan handle kapasitas lumbung basah
     */
    private function updateSortirStatus(array $sortirIds, string $status, bool $returnLumbungCapacity = false, bool $deductLumbungCapacity = false): void
    {
        if (empty($sortirIds)) {
            return;
        }

        // Update status sortiran
        \App\Models\Sortiran::whereIn('id', $sortirIds)
            ->update(['status' => $status]);

        // Handle kapasitas lumbung basah jika diperlukan
        if ($returnLumbungCapacity || $deductLumbungCapacity) {
            $sortirans = \App\Models\Sortiran::whereIn('id', $sortirIds)->get();

            foreach ($sortirans as $sortiran) {
                $kapasitasLumbung = \App\Models\KapasitasLumbungBasah::find($sortiran->no_lumbung_basah);

                if ($kapasitasLumbung) {
                    if ($returnLumbungCapacity) {
                        // Kembalikan kapasitas lumbung basah (sortiran selesai)
                        $kapasitasLumbung->increment('kapasitas_sisa', $sortiran->netto_bersih_integer);
                    } elseif ($deductLumbungCapacity) {
                        // Potong kapasitas lumbung basah (sortiran kembali aktif)
                        $kapasitasLumbung->decrement('kapasitas_sisa', $sortiran->netto_bersih_integer);
                    }
                }
            }
        }
    }
}
