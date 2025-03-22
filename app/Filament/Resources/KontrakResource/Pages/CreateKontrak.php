<?php

namespace App\Filament\Resources\KontrakResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KontrakResource;

class CreateKontrak extends CreateRecord
{
    protected static string $resource = KontrakResource::class;

    // Ubah judul halaman "Create Mobil" menjadi "Tambah Mobil"
    function getTitle(): string
    {
        return 'Tambah Kontrak';
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
                ->url(KontrakResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
