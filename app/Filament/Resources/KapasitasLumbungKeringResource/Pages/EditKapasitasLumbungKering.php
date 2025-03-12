<?php

namespace App\Filament\Resources\KapasitasLumbungKeringResource\Pages;

use App\Filament\Resources\KapasitasLumbungKeringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKapasitasLumbungKering extends EditRecord
{
    protected static string $resource = KapasitasLumbungKeringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
