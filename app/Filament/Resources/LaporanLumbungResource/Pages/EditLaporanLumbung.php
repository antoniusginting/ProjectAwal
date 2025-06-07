<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\LaporanLumbungResource;

class EditLaporanLumbung extends EditRecord
{
    protected static string $resource = LaporanLumbungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
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
                ->url(LaporanLumbungResource::getUrl('index')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
    // protected function afterSave(): void
    // {
    //     // Update kapasitas dryer setelah record dan relasi diupdate
    //     $this->record->updateKapasitasDryerAfterSync();
    // }
}
