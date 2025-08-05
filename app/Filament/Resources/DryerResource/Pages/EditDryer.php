<?php

namespace App\Filament\Resources\DryerResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Services\DryerService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\DryerResource;
use App\Services\SortirService;

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

    
    // Property untuk menyimpan dryer asli sebelum edit
    public $originalSortirans = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Simpan dryer asli sebelum form diisi
        $record = $this->getRecord();
        $this->originalSortirans = $record->sortirans->pluck('id')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Ambil dryer yang baru dipilih
        $newSortirans = $record->sortirans->pluck('id')->toArray();

        // Update status dengan membandingkan dryer lama dan baru
        app(SortirService::class)->updateStatusToDryer(
            $newSortirans,           // Dryer yang baru dipilih
            $this->originalSortirans // Dryer yang lama
        );
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
