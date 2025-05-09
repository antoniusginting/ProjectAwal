<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use App\Filament\Resources\LaporanLumbungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanLumbung extends EditRecord
{
    protected static string $resource = LaporanLumbungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
