<?php

namespace App\Filament\Resources\LumbungBasahResource\Pages;

use App\Models\LumbungBasah;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\LumbungBasahResource;

class ViewLumbungBasah extends Page
{
    protected static string $resource = LumbungBasahResource::class;

    protected static string $view = 'filament.resources.lumbung-basah-resource.pages.view-lumbung-basah';

    public $record;
    public $lumbungbasah;

    public function mount($record)
    {
        $this->record = $record;
        $this->lumbungbasah = LumbungBasah::with(['sortirans'])->find($record);
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

}
