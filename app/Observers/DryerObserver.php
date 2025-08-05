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
                    // Jika lumbung ada isinya, status completed - kembalikan kapasitas
                    $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
                    $dryer->status = 'completed';
                    $dryer->saveQuietly();

                    // Sortiran selesai, status jadi completed dan kembalikan kapasitas lumbung basah
                    $this->updateSortirStatus($sortirIds, 'completed', true);
                }
            }
        }

        // Jika dari ada laporan ke null (dryer dihapus dari laporan)
        elseif (!is_null($oldLaporanId) && is_null($newLaporanId)) {
            // Jika status sebelumnya completed, berarti kapasitas sudah dikembalikan
            // Sekarang harus dipotong lagi karena kembali ke processing
            if ($oldStatus === 'completed') {
                $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                // Sortiran kembali ke in_dryer dan potong kapasitas lumbung basah lagi
                $this->updateSortirStatus($sortirIds, 'in_dryer', false, true);
            }
            // Jika status sebelumnya pending, kapasitas tidak pernah dikembalikan
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
                    // Jika status lama completed, potong kapasitas karena akan jadi pending
                    if ($oldStatus === 'completed') {
                        $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);
                        // Sortiran kembali ke in_dryer dan potong kapasitas lumbung basah
                        $this->updateSortirStatus($sortirIds, 'in_dryer', false, true);
                    }
                    // Jika dari pending ke pending lain, tidak ada perubahan

                    $dryer->status = 'pending';
                    $dryer->saveQuietly();
                } else {
                    // Lumbung baru ada isinya - status completed  
                    // Jika status lama pending, kembalikan kapasitas karena akan jadi completed
                    if ($oldStatus === 'pending') {
                        $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);
                        // Sortiran selesai, status jadi completed dan kembalikan kapasitas lumbung basah
                        $this->updateSortirStatus($sortirIds, 'completed', true);
                    }
                    // Jika dari completed ke completed lain, tidak ada perubahan kapasitas

                    $dryer->status = 'completed';
                    $dryer->saveQuietly();
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
