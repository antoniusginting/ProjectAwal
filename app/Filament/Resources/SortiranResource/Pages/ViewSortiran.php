<?php

namespace App\Filament\Resources\SortiranResource\Pages;

use App\Models\Sortiran;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\SortiranResource;

class ViewSortiran extends Page
{
    protected static string $resource = SortiranResource::class;

    public $record;
    public $sortiran;

    public function mount($record)
    {
        $this->record = $record;
        $this->sortiran = Sortiran::with(['pembelian'])->find($record);
    }

    public function getHeaderActions() :array
    {
        return[
            Action::make('print')
            ->label(__("print"))
            ->icon('heroicon-o-printer')
            ->requiresConfirmation()
        ];
    }

    protected static string $view = 'filament.resources.sortiran-resource.pages.view-sortiran';

}
