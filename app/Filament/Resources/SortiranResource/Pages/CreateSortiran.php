<?php

namespace App\Filament\Resources\SortiranResource\Pages;

use Filament\Actions;
use App\Models\Sortiran;
use Filament\Actions\Action;
use App\Services\SortirService;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\SortiranResource;
use Illuminate\Validation\ValidationException;

class CreateSortiran extends CreateRecord
{
    protected static string $resource = SortiranResource::class;

    function getTitle(): string
    {
        return 'Tambah Sortiran';
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Double check sebelum create untuk antisipasi race condition
        if (isset($data['id_pembelian'])) {
            $exists = Sortiran::where('id_pembelian', $data['id_pembelian'])->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'id_pembelian' => ['ID Pembelian ini sudah digunakan oleh user lain. Silakan refresh halaman dan pilih ID yang berbeda.']
                ]);
            }
        }

        return $data;
    }
    public function getSubheading(): ?string
    {
        $nextId = (Sortiran::max('id') ?? 0) + 1;
        $noSortiran = 'S' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return "{$noSortiran}";
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

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(SortirService::class);

        try {
            return $service->create($data);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal Membuat Sortiran')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            throw $e;
        }
    }
}
