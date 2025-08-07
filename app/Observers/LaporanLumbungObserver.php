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

                    // FIXED: Sortiran selesai, status jadi completed tapi JANGAN kembalikan kapasitas lumbung basah
                    // Karena sudah dikembalikan saat masuk dryer
                    $this->updateSortirStatus($sortirIds, 'completed');
                }
            }

            // Dari lumbung ada isi ke kosong
            elseif (!empty(trim($oldLumbung)) && empty(trim($newLumbung))) {
                // completed → pending
                if ($oldStatus === 'completed') {
                    $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                    $dryer->update(['status' => 'pending']);

                    // FIXED: Sortiran kembali ke in_dryer tapi JANGAN potong kapasitas lumbung basah
                    // Karena kapasitas lumbung basah sudah dikembalikan saat masuk dryer dan tidak perlu dipotong lagi
                    $this->updateSortirStatus($sortirIds, 'in_dryer');
                }
            }
        }
    }

    /**
     * Update status sortiran TANPA handle kapasitas lumbung basah
     * Karena kapasitas lumbung basah sudah dihandle saat sortiran masuk/keluar dryer
     */
    private function updateSortirStatus(array $sortirIds, string $status): void
    {
        if (empty($sortirIds)) {
            return;
        }

        // Update status sortiran saja, TIDAK mengubah kapasitas lumbung basah
        \App\Models\Sortiran::whereIn('id', $sortirIds)
            ->update(['status' => $status]);
    }
}
