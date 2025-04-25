<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPenjualan extends EditRecord
{
    protected static string $resource = PenjualanResource::class;

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
                ->url(PenjualanResource::getUrl('index')),
        ];
    }
    // Di class EditRecord atau CreateRecord Anda
    protected static string $pembelianRedirectField = 'bruto';
    protected static bool $brutoChanged = false;

    // Override method mutateFormDataBeforeSave untuk mendeteksi perubahan
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan nilai original dari field bruto
        $originalBruto = $this->record->bruto ?? null;

        // Catat nilai baru
        $newBruto = $data['bruto'] ?? null;

        // Set flag jika bruto berubah
        static::$brutoChanged = ($originalBruto !== $newBruto);

        return $data;
    }

    // Override getRedirectUrl untuk memeriksa flag perubahan
    protected function getRedirectUrl(): string
    {
        if (static::$brutoChanged) {
            return $this->getResource()::getUrl('view-penjualan', ['record' => $this->record]);
        }

        return $this->getResource()::getUrl('index');
    }
    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    // }
}
