<?php

namespace App\Filament\Resources\KapasitasLumbungKeringResource\Pages;

use App\Filament\Resources\KapasitasLumbungKeringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKapasitasLumbungKerings extends ListRecords
{
    protected static string $resource = KapasitasLumbungKeringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
