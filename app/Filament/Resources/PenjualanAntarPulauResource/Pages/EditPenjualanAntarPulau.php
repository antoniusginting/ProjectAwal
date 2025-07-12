<?php

namespace App\Filament\Resources\PenjualanAntarPulauResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PenjualanAntarPulauResource;

class EditPenjualanAntarPulau extends EditRecord
{
    protected static string $resource = PenjualanAntarPulauResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Ubah')
                ->action(fn() => $this->save()), // Menggunakan fungsi simpan manual
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(PenjualanAntarPulauResource::getUrl('index')),
        ];
    }
}
