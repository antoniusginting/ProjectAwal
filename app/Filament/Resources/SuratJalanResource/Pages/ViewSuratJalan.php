<?php

namespace App\Filament\Resources\SuratJalanResource\Pages;

use App\Models\SuratJalan;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\SuratJalanResource;

class ViewSuratJalan extends Page
{
    protected static string $resource = SuratJalanResource::class;

    public $record;
    public $suratjalan;

    public function mount($record)
    {
        $this->record = $record;
        $this->suratjalan = SuratJalan::with(['tronton','kontrak2','kontrak','tronton.penjualan1','alamat.kontrak'])->find($record);
    }

    public function getHeaderActions() :array
    {
        return[
            Action::make('print')
            ->label(__("print"))
            ->icon('heroicon-o-printer')
            ->url(route("PRINT.SURATJALAN",['id'=>$this->record]))
            ->extraAttributes([
                'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.SORTIRAN", ['id' => $this->record]) . "', '_blank'); }"
            ])
            ->openUrlInNewTab()
        ];
    }

    protected static string $view = 'filament.resources.surat-jalan-resource.pages.view-surat-jalan';
}
