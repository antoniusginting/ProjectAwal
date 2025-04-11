<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\PembelianResource;
use App\Models\Pembelian;

class ViewPembelian extends Page
{
    protected static string $resource = PembelianResource::class;

    public $record;
    public $pembelian;

    public function mount($record)
    {
        $this->record = $record;
        $this->pembelian = Pembelian::with(['supplier'])->find($record);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label(__("print"))
                ->icon('heroicon-o-printer')
                ->url(route("PRINT.PEMBELIAN", ['id' => $this->record]))
                ->extraAttributes([
                    'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.PEMBELIAN", ['id' => $this->record]) . "', '_blank'); }"
                ])
                ->openUrlInNewTab()
        ];
    }


    protected static string $view = 'filament.resources.pembelian-resource.pages.view-pembelian';
}
