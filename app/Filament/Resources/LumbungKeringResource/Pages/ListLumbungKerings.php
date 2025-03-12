<?php

namespace App\Filament\Resources\LumbungKeringResource\Pages;

use App\Filament\Resources\LumbungKeringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLumbungKerings extends ListRecords
{
    protected static string $resource = LumbungKeringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
