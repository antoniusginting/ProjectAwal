<?php

namespace App\Observers;

use App\Models\Dryer;
use App\Models\KapasitasDryer;
use App\Models\LaporanLumbung;
use Illuminate\Support\Facades\DB;

class DryerObserver
{
    public function updated(Dryer $dryer)
    {
        // Cek apakah laporan_lumbung_id berubah
        if ($dryer->isDirty('laporan_lumbung_id')) {
            $this->handleLaporanLumbungIdChange($dryer);
        }
    }

    private function handleLaporanLumbungIdChange(Dryer $dryer): void
    {
        $oldLaporanId = $dryer->getOriginal('laporan_lumbung_id');
        $newLaporanId = $dryer->laporan_lumbung_id;
        $oldStatus = $dryer->getOriginal('status');

        $kapasitas = KapasitasDryer::find($dryer->id_kapasitas_dryer);

        if (!$kapasitas) {
            return;
        }

        // Ambil sortiran yang terkait dengan dryer ini
        $sortirIds = $dryer->sortirans->pluck('id')->toArray();

        // Jika dari null ke ada laporan (dryer dipilih untuk laporan)
        if (is_null($oldLaporanId) && !is_null($newLaporanId)) {
            // Ambil data laporan lumbung
            $laporanLumbung = LaporanLumbung::find($newLaporanId);

            if ($laporanLumbung) {
                // Cek apakah field lumbung kosong atau null
                if (empty(trim($laporanLumbung->lumbung))) {
                    // Jika lumbung kosong, status pending - TIDAK kembalikan kapasitas
                    $dryer->status = 'pending';
                    $dryer->saveQuietly();

                    // Status sortiran tetap in_dryer karena masih dalam dryer (pending)
                    $this->updateSortirStatus($sortirIds, 'in_dryer');
                } else {
                    // Jika lumbung ada isinya, status completed - kembalikan kapasitas DRYER saja
                    $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
                    $dryer->status = 'completed';
                    $dryer->saveQuietly();

                    // FIXED: Sortiran selesai, status jadi completed tapi JANGAN kembalikan kapasitas lumbung basah
                    // Karena sudah dikembalikan saat masuk dryer
                    $this->updateSortirStatus($sortirIds, 'completed');
                }
            }
        }

        // Jika dari ada laporan ke null (dryer dihapus dari laporan)
        elseif (!is_null($oldLaporanId) && is_null($newLaporanId)) {
            // Jika status sebelumnya completed, berarti kapasitas dryer sudah dikembalikan
            // Sekarang harus dipotong lagi karena kembali ke processing
            if ($oldStatus === 'completed') {
                $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                // FIXED: Sortiran kembali ke in_dryer tapi JANGAN potong kapasitas lumbung basah lagi
                // Karena kapasitas lumbung basah sudah dikembalikan saat pertama masuk dryer
                $this->updateSortirStatus($sortirIds, 'in_dryer');
            }
            // Jika status sebelumnya pending, kapasitas dryer tidak pernah dikembalikan
            // Sortiran tetap in_dryer, tidak ada perubahan kapasitas

            // Update status ke processing
            $dryer->status = 'processing';
            $dryer->saveQuietly();
        }

        // Jika dari laporan A ke laporan B (pindah laporan)
        elseif (!is_null($oldLaporanId) && !is_null($newLaporanId) && $oldLaporanId !== $newLaporanId) {
            $newLaporanLumbung = LaporanLumbung::find($newLaporanId);

            if ($newLaporanLumbung) {
                // Cek kondisi lumbung baru
                if (empty(trim($newLaporanLumbung->lumbung))) {
                    // Lumbung baru kosong - status pending
                    // Jika status lama completed, potong kapasitas dryer karena akan jadi pending
                    if ($oldStatus === 'completed') {
                        $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                        // FIXED: Sortiran kembali ke in_dryer tapi JANGAN potong kapasitas lumbung basah
                        $this->updateSortirStatus($sortirIds, 'in_dryer');
                    }
                    // Jika dari pending ke pending lain, tidak ada perubahan

                    $dryer->status = 'pending';
                    $dryer->saveQuietly();
                } else {
                    // Lumbung baru ada isinya - status completed  
                    // Jika status lama pending, kembalikan kapasitas dryer karena akan jadi completed
                    if ($oldStatus === 'pending') {
                        $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
                        // FIXED: Sortiran selesai, status jadi completed tapi JANGAN kembalikan kapasitas lumbung basah
                        $this->updateSortirStatus($sortirIds, 'completed');
                    }
                    // Jika dari completed ke completed lain, tidak ada perubahan kapasitas

                    $dryer->status = 'completed';
                    $dryer->saveQuietly();
                }
            }
        }
    }

    /**
     * Update status sortiran TANPA handle kapasitas lumbung basah
     * Karena kapasitas lumbung basah sudah dikembalikan saat sortiran masuk dryer
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
