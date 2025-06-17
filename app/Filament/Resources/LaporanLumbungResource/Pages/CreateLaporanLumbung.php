<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LaporanLumbungResource;

class CreateLaporanLumbung extends CreateRecord
{
    protected static string $resource = LaporanLumbungResource::class;

    function getTitle(): string
    {
        return 'Tambah Laporan Lumbung';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Tambah')
                ->action(fn() => $this->create()), // Gunakan method bawaan Filament
            // Action::make('cancel')
            //     ->label('Batal')
            //     ->color('gray')
            //     ->url(LaporanLumbungResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
    // protected function afterCreate(): void
    // {
    //     // Update kapasitas dryer setelah record dan relasi dibuat
    //     $this->record->updateKapasitasDryerAfterSync();
    // }
}
