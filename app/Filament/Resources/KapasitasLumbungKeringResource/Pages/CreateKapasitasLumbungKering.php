<?php

namespace App\Filament\Resources\KapasitasLumbungKeringResource\Pages;

use App\Filament\Resources\KapasitasLumbungBasahResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KapasitasLumbungKeringResource;

class CreateKapasitasLumbungKering extends CreateRecord
{
    protected static string $resource = KapasitasLumbungKeringResource::class;
    // Ubah judul halaman "Create Kapasitas lumbung basah" menjadi "Tambah Kapasitas lumbung basah"
    function getTitle(): string
    {
        return 'Tambah Kapasitas Lumbung Kering';
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
                ->url(KapasitasLumbungKeringResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
