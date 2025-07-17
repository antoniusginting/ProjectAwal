<?php

namespace App\Filament\Resources\KapasitasKontrakJualResource\Pages;

use App\Filament\Resources\KapasitasKontrakJualResource;
use App\Models\KapasitasKontrakJual;
use Filament\Resources\Pages\Page;

class ViewKapasitasKontrakJual extends Page
{
    protected static string $resource = KapasitasKontrakJualResource::class;

    protected static string $view = 'filament.resources.kapasitas-kontrak-jual-resource.pages.view-kapasitas-kontrak-jual';
    public $record;
    public $kontrakLuar;
    function getTitle(): string
    {
        return 'View Kapasitas Kontrak Jual';
    }
    public function mount($record)
    {
        $this->record = $record;
        $this->kontrakLuar = KapasitasKontrakJual::with(['penjualanLuar','suratJalan'])->find($record);
    }
}
