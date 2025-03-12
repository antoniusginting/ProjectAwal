<?php

namespace App\Filament\Resources\LumbungKeringResource\Pages;

use App\Filament\Resources\LumbungKeringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLumbungKering extends EditRecord
{
    protected static string $resource = LumbungKeringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
