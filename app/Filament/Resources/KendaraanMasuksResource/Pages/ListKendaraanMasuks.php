<?php

namespace App\Filament\Resources\KendaraanMasuksResource\Pages;

use App\Filament\Resources\KendaraanMasuksResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKendaraanMasuks extends ListRecords
{
    protected static string $resource = KendaraanMasuksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
