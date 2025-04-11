<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\PenjualanResource;
use App\Models\Penjualan;

class ViewPenjualan extends Page
{
    protected static string $resource = PenjualanResource::class;

    public $record;
    public $penjualan;

    public function mount($record)
    {
        $this->record = $record;
        $this->penjualan = Penjualan::find($record);
    }

    public function getHeaderActions() :array
    {
        return[
            Action::make('print')
            ->label(__("print"))
            ->icon('heroicon-o-printer')
            //->url(route("PRINT.SORTIRAN",['id'=>$this->record]))
            // ->extraAttributes([
            //     'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.SORTIRAN", ['id' => $this->record]) . "', '_blank'); }"
            // ])
            // ->openUrlInNewTab()
        ];
    }

    protected static string $view = 'filament.resources.penjualan-resource.pages.view-penjualan';
}
