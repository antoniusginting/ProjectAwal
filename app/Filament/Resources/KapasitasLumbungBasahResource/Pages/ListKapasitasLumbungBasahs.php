<?php

namespace App\Filament\Resources\KapasitasLumbungBasahResource\Pages;

use App\Filament\Resources\KapasitasLumbungBasahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKapasitasLumbungBasahs extends ListRecords
{
    protected static string $resource = KapasitasLumbungBasahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
