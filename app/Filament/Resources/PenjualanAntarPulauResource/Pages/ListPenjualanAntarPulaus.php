<?php

namespace App\Filament\Resources\PenjualanAntarPulauResource\Pages;

use App\Filament\Resources\PenjualanAntarPulauResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenjualanAntarPulaus extends ListRecords
{
    protected static string $resource = PenjualanAntarPulauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
