<?php

namespace App\Filament\Resources\KapasitasLumbungBasahResource\Pages;

use App\Filament\Resources\KapasitasLumbungBasahResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateKapasitasLumbungBasah extends CreateRecord
{
    protected static string $resource = KapasitasLumbungBasahResource::class;

    // Ubah judul halaman "Create Kapasitas lumbung basah" menjadi "Tambah Kapasitas lumbung basah"
    function getTitle(): string
    {
        return 'Tambah Kapasitas Lumbung Basah';
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
                ->url(KapasitasLumbungBasahResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
