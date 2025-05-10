<?php

namespace App\Filament\Resources\DryerResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\DryerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDryer extends CreateRecord
{
    protected static string $resource = DryerResource::class;

    // Ubah judul halaman "Create Kapasitas lumbung basah" menjadi "Tambah Kapasitas lumbung basah"
    function getTitle(): string
    {
        return 'Tambah Dryer/Panggangan';
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
                ->url(DryerResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
