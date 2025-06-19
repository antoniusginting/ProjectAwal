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

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
    // Property untuk menyimpan dryer asli sebelum edit
    public $originalDryers = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Simpan dryer asli sebelum form diisi
        $record = $this->getRecord();
        $this->originalDryers = $record->dryers->pluck('id')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Ambil dryer yang baru dipilih
        $newDryers = $record->dryers->pluck('id')->toArray();

        // Update status dengan membandingkan dryer lama dan baru
        app(DryerService::class)->updateStatusToCompleted(
            $newDryers,           // Dryer yang baru dipilih
            $this->originalDryers // Dryer yang lama
        );
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
