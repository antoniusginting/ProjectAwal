<?php

namespace App\Filament\Resources\PembelianAntarPulauResource\Pages;

use App\Filament\Resources\PembelianAntarPulauResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembelianAntarPulaus extends ListRecords
{
    protected static string $resource = PembelianAntarPulauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),
        ];
    }
}
