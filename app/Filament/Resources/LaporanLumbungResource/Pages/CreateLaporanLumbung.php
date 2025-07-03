<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Services\DryerService;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LaporanLumbungResource;

class CreateLaporanLumbung extends CreateRecord
{
    protected static string $resource = LaporanLumbungResource::class;

    function getTitle(): string
    {
        return 'Tambah Lumbung Kering';
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
                ->url(LaporanLumbungResource::getUrl('index')), // Redirect ke tabel utama
        ];
    }
    // protected function afterCreate(): void
    // {
    //     $this->syncPenjualanWithTipe();

    //     $record = $this->getRecord();
    //     $selectedDryers = $record->dryers->pluck('id')->toArray();

    //     // Update status dryers yang dipilih
    //     app(DryerService::class)->updateStatusToCompleted($selectedDryers, []);
    // }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }

    //MENAMBAHKAN FIELD tipe_penjualan PADA PIVOT
    // private function syncPenjualanWithTipe(): void
    // {
    //     $record = $this->record;
    //     $penjualanIds = $this->data['penjualan_ids'] ?? [];
    //     $tipe = $this->data['tipe_penjualan'] ?? 'masuk';

    //     // Sync dengan pivot data
    //     $syncData = [];
    //     foreach ($penjualanIds as $id) {
    //         $syncData[$id] = [
    //             'tipe_penjualan' => $tipe,
    //             'created_at' => now(),
    //             'updated_at' => now()
    //         ];
    //     }

    //     $record->penjualans()->sync($syncData);
    // }
}
