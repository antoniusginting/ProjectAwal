<?php

namespace App\Filament\Resources\TransferResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TransferResource;

class EditTransfer extends EditRecord
{
    protected static string $resource = TransferResource::class;

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
                ->url(TransferResource::getUrl('index')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
