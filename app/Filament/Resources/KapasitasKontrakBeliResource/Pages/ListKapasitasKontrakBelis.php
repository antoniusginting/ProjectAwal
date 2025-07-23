<?php

namespace App\Filament\Resources\KapasitasKontrakBeliResource\Pages;

use App\Filament\Resources\KapasitasKontrakBeliResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKapasitasKontrakBelis extends ListRecords
{
    protected static string $resource = KapasitasKontrakBeliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }

    function getTitle(): string
    {
        return 'Daftar Kapasitas Kontrak Beli';
    }
}
