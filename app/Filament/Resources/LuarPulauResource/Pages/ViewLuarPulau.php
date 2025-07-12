<?php

namespace App\Filament\Resources\LuarPulauResource\Pages;

use App\Filament\Resources\LuarPulauResource;
use App\Models\LuarPulau;
use Filament\Resources\Pages\Page;

class ViewLuarPulau extends Page
{
    protected static string $resource = LuarPulauResource::class;

    protected static string $view = 'filament.resources.luar-pulau-resource.pages.view-luar-pulau';

    public $record;
    public $luarPulau;
    function getTitle(): string
    {
        return 'View Kontrak';
    }
    public function mount($record)
    {
        $this->record = $record;
        $this->luarPulau = LuarPulau::with(['pembelianLuar'])->find($record);
    }
}
