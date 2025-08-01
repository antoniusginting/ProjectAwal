<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Tambah User';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Tambah Data')
                ->action(function () {
                    $this->create();

                    Notification::make()
                        ->title('User berhasil ditambahkan')
                        ->success()
                        ->send();

                    // Redirect ke halaman index User
                    return redirect()->to(UserResource::getUrl('index'));
                }),
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(UserResource::getUrl('index')),
        ];
    }
}
