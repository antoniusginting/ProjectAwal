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
        return 'Tambah Laporan Lumbung';
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $selectedDryers = $record->dryers->pluck('id')->toArray();

        // Update status dryers yang dipilih
        app(DryerService::class)->updateStatusToCompleted($selectedDryers, []);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
