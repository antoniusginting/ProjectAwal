<?php

namespace App\Filament\Resources\DryerResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Services\DryerService;
use App\Services\SortirService;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
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


    protected function handleRecordCreation(array $data): Model
    {
        $service = app(DryerService::class);

        try {
            return $service->create($data);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal Membuat Dryer')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            throw $e;
        }
    }




    

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $selectedSortirans = $record->sortirans->pluck('id')->toArray();

        // Update status dryers yang dipilih
        app(SortirService::class)->updateStatusToDryer($selectedSortirans, []);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
