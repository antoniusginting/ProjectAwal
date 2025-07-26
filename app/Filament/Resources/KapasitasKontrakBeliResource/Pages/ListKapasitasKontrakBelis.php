<?php

namespace App\Filament\Resources\KapasitasKontrakBeliResource\Pages;

use App\Filament\Resources\KapasitasKontrakBeliResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKapasitasKontrakBelis extends ListRecords
{
    protected static string $resource = KapasitasKontrakBeliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),

            // Tombol untuk GORONTALO
            Actions\Action::make('gorontalo')
                ->label('Laporan GORONTALO')
                ->icon('heroicon-o-document-chart-bar')
                ->color('success')
                ->url(fn(): string => KapasitasKontrakBeliResource::getUrl('gorontalo')),

            // Tombol untuk MAKASSAR  
            Actions\Action::make('makassar')
                ->label('Laporan MAKASSAR')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn(): string => KapasitasKontrakBeliResource::getUrl('makassar')),
        ];
    }

    function getTitle(): string
    {
        return 'Daftar Kapasitas Kontrak Beli';
    }
}
