<?php

namespace App\Filament\Resources\KapasitasDryerResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KapasitasDryerResource;
use App\Models\KapasitasDryer;

class CreateKapasitasDryer extends CreateRecord
{
    protected static string $resource = KapasitasDryerResource::class;

    // Ubah judul halaman "Create Kapasitas lumbung basah" menjadi "Tambah Kapasitas lumbung basah"
    function getTitle(): string
    {
        return 'Tambah Kapasitas Dryer';
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
                ->url(KapasitasDryerResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
