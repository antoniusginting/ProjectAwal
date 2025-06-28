<?php

namespace App\Filament\Resources\SiloResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\SiloResource;
use Filament\Resources\Pages\EditRecord;

class EditSilo extends EditRecord
{
    protected static string $resource = SiloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    // Ubah judul halaman "Create Kapasitas lumbung basah" menjadi "Tambah Kapasitas lumbung basah"
    function getTitle(): string
    {
        return 'Edit Silo/Kontrak';
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Ubah')
                ->action(fn() => $this->save()), // Menggunakan fungsi simpan manual
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(SiloResource::getUrl('index')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
