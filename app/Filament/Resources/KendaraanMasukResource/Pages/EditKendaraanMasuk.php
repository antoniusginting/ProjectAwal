<?php

namespace App\Filament\Resources\KendaraanMasukResource\Pages;

use App\Filament\Resources\KendaraanMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKendaraanMasuk extends EditRecord
{
    protected static string $resource = KendaraanMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
