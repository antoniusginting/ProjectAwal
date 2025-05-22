<?php

namespace App\Filament\Resources\LuarResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\LuarResource;
use Filament\Resources\Pages\EditRecord;

class EditLuar extends EditRecord
{
    protected static string $resource = LuarResource::class;

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
                ->url(LuarResource::getUrl('index')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
