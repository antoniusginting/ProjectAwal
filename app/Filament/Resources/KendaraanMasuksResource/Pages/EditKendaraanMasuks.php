<?php

namespace App\Filament\Resources\KendaraanMasuksResource\Pages;

use App\Filament\Resources\KendaraanMasuksResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditKendaraanMasuks extends EditRecord
{
    protected static string $resource = KendaraanMasuksResource::class;

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
            ->action(fn () => $this->save()), // Menggunakan fungsi simpan manual
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(KendaraanMasuksResource::getUrl('index')),
    ];
}
protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
