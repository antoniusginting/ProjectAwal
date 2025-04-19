<?php

namespace App\Filament\Resources\KendaraanMuatResource\Pages;

use App\Filament\Resources\KendaraanMuatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKendaraanMuats extends ListRecords
{
    protected static string $resource = KendaraanMuatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
