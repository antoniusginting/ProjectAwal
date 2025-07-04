<?php

namespace App\Filament\Resources\TransferResource\Pages;

use App\Filament\Resources\TransferResource;
use App\Models\Transfer;
use Filament\Resources\Pages\Page;

class ViewTransfer extends Page
{
    protected static string $resource = TransferResource::class;

    protected static string $view = 'filament.resources.transfer-resource.pages.view-transfer';


    public $record;
    public $transfer;
    function getTitle(): string
    {
        return 'View Transfer';
    }
    public function mount($record)
    {
        $this->record = $record;
        $this->transfer = Transfer::with(['user'])->find($record);
    }
}
