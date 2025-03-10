<?php

namespace App\Filament\Resources\KapasitasDryerResource\Pages;

use App\Filament\Resources\KapasitasDryerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKapasitasDryers extends ListRecords
{
    protected static string $resource = KapasitasDryerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
