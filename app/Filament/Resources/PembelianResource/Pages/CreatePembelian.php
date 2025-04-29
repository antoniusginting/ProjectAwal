<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Models\Pembelian;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

     // Ubah judul halaman "Create Mobil" menjadi "Tambah Mobil"
    function getTitle(): string
    {
        return 'Tambah Pembelian';
    }
    // public function getSubheading(): ?string
    // {
    //     $nextId = (Pembelian::max('id') ?? 0) + 1;
    //     $noPembelian = 'B' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    //     return "{$noPembelian}";
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
                ->url(PembelianResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
