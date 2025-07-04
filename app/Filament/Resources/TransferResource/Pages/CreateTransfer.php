<?php

namespace App\Filament\Resources\TransferResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransferResource;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    function getTitle(): string
    {
        return 'Tambah Transfer';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Tambah')
                ->action(fn() => $this->create()), // Gunakan method bawaan Filament
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(TransferResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
