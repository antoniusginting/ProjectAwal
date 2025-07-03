<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Services\DryerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\LaporanLumbungResource;

class EditLaporanLumbung extends EditRecord
{
    protected static string $resource = LaporanLumbungResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Ubah')
                ->action(fn() => $this->save()), // Menggunakan fungsi simpan manual
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(LaporanLumbungResource::getUrl('index')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    // Property untuk menyimpan dryer asli sebelum edit
    // public $originalDryers = [];

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     // Simpan dryer asli sebelum form diisi
    //     $record = $this->getRecord();

    //     //AMBIL DARI PIVOT TABLE UNTUK LANGSIR
    //     if ($record->penjualans()->exists()) {
    //         // Ambil tipe dari pivot table (asumsi semua penjualan punya tipe yang sama)
    //         $firstPenjualan = $record->penjualans()->first();
    //         if ($firstPenjualan) {
    //             $data['tipe_penjualan'] = $firstPenjualan->pivot->tipe_penjualan;
    //         }
    //     }

    //     $this->originalDryers = $record->dryers->pluck('id')->toArray();

    //     return $data;
    // }

    // protected function afterSave(): void
    // {
    //     $this->syncPenjualanWithTipe();

    //     $record = $this->getRecord();

    //     // Ambil dryer yang baru dipilih
    //     $newDryers = $record->dryers->pluck('id')->toArray();

    //     // Update status dengan membandingkan dryer lama dan baru
    //     app(DryerService::class)->updateStatusToCompleted(
    //         $newDryers,           // Dryer yang baru dipilih
    //         $this->originalDryers // Dryer yang lama
    //     );
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
