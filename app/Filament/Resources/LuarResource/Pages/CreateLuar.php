<?php

namespace App\Filament\Resources\LuarResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\LuarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLuar extends CreateRecord
{
    protected static string $resource = LuarResource::class;

    // Ubah judul halaman "Create Mobil" menjadi "Tambah Mobil"
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
                ->url(LuarResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
