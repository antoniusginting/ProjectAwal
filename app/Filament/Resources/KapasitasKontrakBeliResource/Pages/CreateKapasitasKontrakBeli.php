<?php

namespace App\Filament\Resources\KapasitasKontrakBeliResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KapasitasKontrakBeliResource;

class CreateKapasitasKontrakBeli extends CreateRecord
{
    protected static string $resource = KapasitasKontrakBeliResource::class;

    function getTitle(): string
    {
        return 'Tambah Kapasitas Kontrak Beli';
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
                ->url(KapasitasKontrakBeliResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
