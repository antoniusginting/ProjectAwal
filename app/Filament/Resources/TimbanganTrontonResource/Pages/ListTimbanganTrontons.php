<?php

namespace App\Filament\Resources\TimbanganTrontonResource\Pages;

use App\Filament\Resources\TimbanganTrontonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimbanganTrontons extends ListRecords
{
    protected static string $resource = TimbanganTrontonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
