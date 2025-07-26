<?php

namespace App\Filament\Resources\KapasitasKontrakBeliResource\Pages;

use App\Filament\Resources\KapasitasKontrakBeliResource;
use App\Models\KapasitasKontrakBeli;
use Filament\Resources\Pages\Page;

class GorontaloGabungan extends Page
{
    protected static string $resource = KapasitasKontrakBeliResource::class;

    protected static string $view = 'filament.resources.kapasitas-kontrak-beli-resource.pages.gorontalo-gabungan';
    public $kontrakBelis;
    public function mount(): void
    {
        // Ambil semua kontrak GORONTALO
        $this->kontrakBelis = KapasitasKontrakBeli::with(['pembelianLuar'])
            ->where('nama', 'GORONTALO')
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
        return 'Laporan Gabungan GORONTALO';
    }
}
