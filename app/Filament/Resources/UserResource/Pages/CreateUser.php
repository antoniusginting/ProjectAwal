<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    function getTitle(): string
    {
        return 'Tambah User';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Tambah Data')
                ->action(fn () => $this->create()), // Menggunakan fungsi simpan manual
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(UserResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }
}
