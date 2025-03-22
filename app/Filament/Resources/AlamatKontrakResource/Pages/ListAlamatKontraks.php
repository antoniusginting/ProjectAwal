<?php

namespace App\Filament\Resources\AlamatKontrakResource\Pages;

use App\Filament\Resources\AlamatKontrakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlamatKontraks extends ListRecords
{
    protected static string $resource = AlamatKontrakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
