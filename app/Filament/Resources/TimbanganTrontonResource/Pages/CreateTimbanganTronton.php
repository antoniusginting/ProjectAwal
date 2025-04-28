<?php

namespace App\Filament\Resources\TimbanganTrontonResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TimbanganTrontonResource;
use App\Models\TimbanganTronton;

class CreateTimbanganTronton extends CreateRecord
{
    protected static string $resource = TimbanganTrontonResource::class;

    // Ubah judul halaman "Create Mobil" menjadi "Tambah Mobil"
    function getTitle(): string
    {
        return 'Tambah Laporan Penjualan';
    }
    public function getSubheading(): ?string
    {
        $nextId = (TimbanganTronton::max('id') ?? 0) + 1;
        $noPenjualan = 'P' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return "{$noPenjualan}";
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
                ->url(TimbanganTrontonResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
