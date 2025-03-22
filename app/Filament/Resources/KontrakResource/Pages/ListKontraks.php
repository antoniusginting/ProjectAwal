<?php

namespace App\Filament\Resources\KontrakResource\Pages;

use App\Filament\Resources\KontrakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKontraks extends ListRecords
{
    protected static string $resource = KontrakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
