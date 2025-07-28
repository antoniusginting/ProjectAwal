<?php

namespace App\Filament\Resources\KapasitasKontrakBeliResource\Pages;

use App\Filament\Resources\KapasitasKontrakBeliResource;
use App\Models\KapasitasKontrakBeli;
use Filament\Resources\Pages\Page;

class MakassarGabungan extends Page
{
    protected static string $resource = KapasitasKontrakBeliResource::class;

    protected static string $view = 'filament.resources.kapasitas-kontrak-beli-resource.pages.makassar-gabungan';

    public $kontrakBelis;
    public function mount(): void
    {
        // Ambil semua kontrak MAKASSAR
        $this->kontrakBelis = KapasitasKontrakBeli::with(['pembelianLuar'])
            ->where('nama', 'MAKASSAR')
            ->get();
    }

    protected function getViewData(): array
    {
        return [
            'kontrakBelis' => $this->kontrakBelis,
        ];
    }

    public function getTitle(): string
    {
        return 'Laporan Gabungan Jagung MAKASSAR';
    }
}
