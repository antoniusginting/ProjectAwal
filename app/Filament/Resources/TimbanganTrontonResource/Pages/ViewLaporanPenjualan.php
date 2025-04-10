<?php

namespace App\Filament\Resources\TimbanganTrontonResource\Pages;

use App\Filament\Resources\TimbanganTrontonResource;
use App\Models\TimbanganTronton;
use Filament\Resources\Pages\Page;

class ViewLaporanPenjualan extends Page
{
    protected static string $resource = TimbanganTrontonResource::class;

    public $record;
    public $timbangantronton;

    public function mount($record)
    {
        $this->record = $record;
        $this->timbangantronton = TimbanganTronton::with(['penjualan1'])->find($record);
    }

    // public function getHeaderActions() :array
    // {
    //     return[
    //         Action::make('print')
    //         ->label(__("print"))
    //         ->icon('heroicon-o-printer')
    //         ->url(route("PRINT.SURATJALAN",['id'=>$this->record]))
    //         ->extraAttributes([
    //             'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.SORTIRAN", ['id' => $this->record]) . "', '_blank'); }"
    //         ])
    //         ->openUrlInNewTab()
    //     ];
    // }

    protected static string $view = 'filament.resources.timbangan-tronton-resource.pages.view-laporan-penjualan';
}
