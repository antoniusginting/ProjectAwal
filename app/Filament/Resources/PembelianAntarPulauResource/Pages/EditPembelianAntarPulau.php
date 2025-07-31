<?php

namespace App\Filament\Resources\PembelianAntarPulauResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PembelianAntarPulauResource;

class EditPembelianAntarPulau extends EditRecord
{
    protected static string $resource = PembelianAntarPulauResource::class;

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
                ->action(function () {
                    $this->save();

                    // Redirect setelah simpan
                    return redirect()->to(PembelianAntarPulauResource::getUrl('index'));
                }),

            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(PembelianAntarPulauResource::getUrl('index')),
        ];
    }
}
