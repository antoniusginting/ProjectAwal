<?php

namespace App\Observers;

use App\Models\Dryer;
use App\Models\KapasitasDryer;
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

        $kapasitas = KapasitasDryer::find($dryer->id_kapasitas_dryer);

        if (!$kapasitas) {
            return;
        }

        // Jika dari null ke ada laporan (dryer dipilih untuk laporan)
        if (is_null($oldLaporanId) && !is_null($newLaporanId)) {
            // Kembalikan kapasitas karena dryer selesai/masuk laporan
            $kapasitas->increment('kapasitas_sisa', $dryer->total_netto_integer);

            // Update status ke completed
            $dryer->status = 'completed';
            $dryer->saveQuietly();
        }

        // Jika dari ada laporan ke null (dryer dihapus dari laporan)
        elseif (!is_null($oldLaporanId) && is_null($newLaporanId)) {
            // Potong kapasitas lagi karena dryer kembali ke status proses
            $kapasitas->decrement('kapasitas_sisa', $dryer->total_netto_integer);

            // Update status ke processing
            $dryer->status = 'processing';
            $dryer->saveQuietly();
        }

        // Jika dari laporan A ke laporan B (pindah laporan)
        elseif (!is_null($oldLaporanId) && !is_null($newLaporanId) && $oldLaporanId !== $newLaporanId) {
            // Tidak perlu ubah kapasitas karena dryer tetap completed
            // Status tetap completed
            $dryer->status = 'completed';
            $dryer->saveQuietly();
        }
    }
}
