<?php

namespace App\Filament\Resources\LumbungBasahResource\Pages;

use App\Filament\Resources\LumbungBasahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLumbungBasahs extends ListRecords
{
    protected static string $resource = LumbungBasahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
