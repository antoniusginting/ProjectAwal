<?php

namespace App\Filament\Resources\DryerResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Services\DryerService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\DryerResource;

class EditDryer extends EditRecord
{
    protected static string $resource = DryerResource::class;
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(\App\Services\DryerService::class);

        try {
            /** @var \App\Models\Dryer $updatedDryer */
            $updatedDryer = $service->update($record, $data);

            return $updatedDryer;
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Gagal Mengubah Dryer')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            throw $e;
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(function (Model $record) {
                    $service = app(DryerService::class);
                    return $service->delete($record);
                }),
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
                ->url(DryerResource::getUrl('index')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
