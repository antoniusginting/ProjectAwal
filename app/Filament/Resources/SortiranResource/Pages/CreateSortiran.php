<?php

namespace App\Filament\Resources\SortiranResource\Pages;

use App\Filament\Resources\SortiranResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSortiran extends CreateRecord
{
    protected static string $resource = SortiranResource::class;

     function getTitle(): string
    {
        return 'Tambah Sortiran';
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
                ->url(SortiranResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
