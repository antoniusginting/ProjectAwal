<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use App\Models\Penjualan;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

     // Ubah judul halaman "Create Mobil" menjadi "Tambah Mobil"
    function getTitle(): string
    {
        return 'Tambah Penjualan';
    }
    // public function getSubheading(): ?string
    // {
    //     $nextId = (Penjualan::max('id') ?? 0) + 1;
    //     $noPenjualan = 'J' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    //     return "{$noPenjualan}";
    // }
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Tambah')
                ->action(fn() => $this->create()), // Gunakan method bawaan Filament
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(PenjualanResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
