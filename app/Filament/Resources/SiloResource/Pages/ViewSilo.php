<?php

namespace App\Filament\Resources\SiloResource\Pages;

use App\Models\Silo;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\SiloResource;

class ViewSilo extends Page
{
    protected static string $resource = SiloResource::class;

    protected static string $view = 'filament.resources.silo-resource.pages.view-silo';


    public $record;
    public $silo;
    function getTitle(): string
    {
        return 'View Kapasitas Silo & Kontrak';
    }
    public function mount($record)
    {
        $this->record = $record;
        $this->silo = Silo::with(['timbanganTrontons', 'laporanLumbungs'])->find($record);
    }
    // public function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('print')
    //             ->label(__("print"))
    //             ->icon('heroicon-o-printer')
    //             ->url(route("PRINT.SILO", ['id' => $this->record]))
    //             ->extraAttributes([
    //                 'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.SILO", ['id' => $this->record]) . "', '_blank'); }"
    //             ])
    //             ->openUrlInNewTab()
    //     ];
    // }
}
