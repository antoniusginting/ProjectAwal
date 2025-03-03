<?php

namespace App\Filament\Resources\SortiranResource\Pages;

use App\Filament\Resources\SortiranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSortirans extends ListRecords
{
    protected static string $resource = SortiranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
