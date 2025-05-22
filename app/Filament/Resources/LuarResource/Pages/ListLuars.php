<?php

namespace App\Filament\Resources\LuarResource\Pages;

use App\Filament\Resources\LuarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLuars extends ListRecords
{
    protected static string $resource = LuarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
