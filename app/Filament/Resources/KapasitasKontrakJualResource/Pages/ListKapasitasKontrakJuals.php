<?php

namespace App\Filament\Resources\KapasitasKontrakJualResource\Pages;

use App\Filament\Resources\KapasitasKontrakJualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKapasitasKontrakJuals extends ListRecords
{
    protected static string $resource = KapasitasKontrakJualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
    function getTitle(): string
    {
        return 'Daftar Kapasitas Kontrak Jual';
    }
}
