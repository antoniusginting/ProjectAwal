<?php

namespace App\Filament\Resources\KendaraanMasuksResource\Pages;

use App\Filament\Resources\KendaraanMasuksResource;
use App\Models\KendaraanMasuks;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateKendaraanMasuks extends CreateRecord
{
    protected static string $resource = KendaraanMasuksResource::class;
    
    // Ubah judul halaman "Create Mobil" menjadi "Tambah Mobil"
    function getTitle(): string
    {
        return 'Tambah Registrasi Kendaraan';
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
                ->url(KendaraanMasuksResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
