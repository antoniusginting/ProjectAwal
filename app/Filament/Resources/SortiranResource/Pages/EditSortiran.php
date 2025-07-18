<?php

namespace App\Filament\Resources\SortiranResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Services\SortirService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\SortiranResource;

class EditSortiran extends EditRecord
{
    protected static string $resource = SortiranResource::class;
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(\App\Services\SortirService::class);

        try {
            /** @var \App\Models\Sortiran $updatedSortiran */
            $updatedSortiran = $service->update($record, $data);

            return $updatedSortiran;
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
    protected function afterSave(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek apakah user memiliki role yang tepat
        if ($user->hasAnyRole(['super_admin'])) {
            $this->record->update(['cek' => false]);
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(function (Model $record) {
                    $service = app(SortirService::class);
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
                ->url(SortiranResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
