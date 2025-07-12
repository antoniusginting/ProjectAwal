<?php

namespace App\Filament\Resources\PembelianAntarPulauResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PembelianAntarPulauResource;

class CreatePembelianAntarPulau extends CreateRecord
{
    protected static string $resource = PembelianAntarPulauResource::class;

     function getTitle(): string
    {
        return 'Tambah Pembelian Antar Pulau';
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
                ->url(PembelianAntarPulauResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
