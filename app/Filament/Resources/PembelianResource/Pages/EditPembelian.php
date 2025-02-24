<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PembelianResource;

class EditPembelian extends EditRecord
{
    protected static string $resource = PembelianResource::class;

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
                ->action(fn () => $this->save()), // Menggunakan fungsi simpan manual
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     // Debug data sebelum disimpan
    //     dd($data);

    //     // Isi jam_keluar dengan waktu sekarang jika masih null
    //     if (is_null($data['jam_keluar'])) {
    //         $data['jam_keluar'] = Carbon::now();
    //     }

    //     return $data;
    // }
}