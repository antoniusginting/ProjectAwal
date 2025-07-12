<?php

namespace App\Filament\Resources\PenjualanAntarPulauResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PenjualanAntarPulauResource;

class CreatePenjualanAntarPulau extends CreateRecord
{
    protected static string $resource = PenjualanAntarPulauResource::class;

      function getTitle(): string
    {
        return 'Tambah Penjualan Antar Pulau';
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
                ->url(PenjualanAntarPulauResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
