<?php

namespace App\Filament\Resources\SiloResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\SiloResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSilo extends CreateRecord
{
    protected static string $resource = SiloResource::class;

    // Ubah judul halaman "Create Kapasitas lumbung basah" menjadi "Tambah Kapasitas lumbung basah"
    function getTitle(): string
    {
        return 'Tambah Silo/Kontrak';
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
                ->url(SiloResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
