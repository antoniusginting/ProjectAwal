<?php

namespace App\Filament\Resources\DryerResource\Pages;

use App\Filament\Resources\DryerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDryers extends ListRecords
{
    protected static string $resource = DryerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
