<?php

namespace App\Filament\Resources\DryerResource\Pages;

use App\Models\Dryer;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\DryerResource;

class ViewDryer extends Page
{
    protected static string $resource = DryerResource::class;

    protected static string $view = 'filament.resources.dryer-resource.pages.view-dryer';

    public $record;
    public $dryer;
    function getTitle(): string
    {
        return 'View Form Perintah Kerja Dryer';
    }
    public function mount($record)
    {
        $this->record = $record;
        $this->dryer = Dryer::with(['lumbung1', 'lumbung2', 'lumbung3', 'lumbung4'])->find($record);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label(__("print"))
                ->icon('heroicon-o-printer')
                ->url(route("PRINT.DRYER", ['id' => $this->record]))
                ->extraAttributes([
                    'onclick' => "if(confirm('Apakah Anda yakin ingin mencetak?')) { window.open('" . route("PRINT.DRYER", ['id' => $this->record]) . "', '_blank'); }"
                ])
                ->openUrlInNewTab()
        ];
    }
}
