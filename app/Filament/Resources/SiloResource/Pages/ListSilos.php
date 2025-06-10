<?php

namespace App\Filament\Resources\SiloResource\Pages;

use App\Filament\Resources\SiloResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSilos extends ListRecords
{
    protected static string $resource = SiloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(''),
        ];
    }
}
