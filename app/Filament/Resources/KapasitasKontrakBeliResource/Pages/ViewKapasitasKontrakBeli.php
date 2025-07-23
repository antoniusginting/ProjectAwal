<?php

namespace App\Filament\Resources\KapasitasKontrakBeliResource\Pages;

use App\Filament\Resources\KapasitasKontrakBeliResource;
use App\Models\KapasitasKontrakBeli;
use Filament\Resources\Pages\Page;

class ViewKapasitasKontrakBeli extends Page
{
    protected static string $resource = KapasitasKontrakBeliResource::class;

    protected static string $view = 'filament.resources.kapasitas-kontrak-beli-resource.pages.view-kapasitas-kontrak-beli';
    public $record;
    public $kontrakBeli;
    function getTitle(): string
    {
        return 'View Kapasitas Kontrak Beli';
    }
    public function mount($record)
    {
        $this->record = $record;
        $this->kontrakBeli = KapasitasKontrakBeli::with(['pembelianLuar'])->find($record);
    }
}
