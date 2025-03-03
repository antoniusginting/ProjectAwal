<?php

namespace App\Filament\Resources\SortiranResource\Pages;

use App\Filament\Resources\SortiranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSortiran extends EditRecord
{
    protected static string $resource = SortiranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
