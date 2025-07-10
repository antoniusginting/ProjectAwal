<?php

namespace App\Filament\Resources\LuarPulauResource\Pages;

use App\Filament\Resources\LuarPulauResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLuarPulaus extends ListRecords
{
    protected static string $resource = LuarPulauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
