<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use App\Filament\Resources\LaporanLumbungResource;
use App\Models\LaporanLumbung;
use Filament\Resources\Pages\Page;

class ViewLaporanLumbung extends Page
{
    protected static string $resource = LaporanLumbungResource::class;

    protected static string $view = 'filament.resources.laporan-lumbung-resource.pages.view-laporan-lumbung';

    public $record;
    public $laporanlumbung;

    public function mount($record)
    {
        $this->record = $record;
        $this->laporanlumbung = LaporanLumbung::with(['dryers'])->find($record);
    }

    public function getHeaderActions(): array
    {
        return [
            // Action::make('print')
            //     ->label(__("print"))
            //     ->icon('heroicon-o-printer')
            //     ->url(route("PRINT.PEMBELIAN", ['id' => $this->record]))
            //     ->extraAttributes([
            //         'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.PEMBELIAN", ['id' => $this->record]) . "', '_blank'); }"
            //     ])
            //     ->openUrlInNewTab()
        ];
    }
}
