<?php

namespace App\Filament\Resources\TimbanganTrontonResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TimbanganTrontonResource;
use App\Models\TimbanganTronton;

class EditTimbanganTronton extends EditRecord
{
    protected static string $resource = TimbanganTrontonResource::class;
    public function getSubheading(): ?string
    {
        // Jika sedang edit, ambil kode yang sudah ada
        if ($this->record && $this->record->exists) {
            return $this->record->kode ?? $this->record->kode;
        }
        // Jika create baru, generate kode baru
        $nextId = (TimbanganTronton::max('id') ?? 0) + 1;
        $noTimbangan = 'P' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        return "{$noTimbangan}";
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Ubah')
                ->action(fn() => $this->save()), // Menggunakan fungsi simpan manual
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(TimbanganTrontonResource::getUrl('index')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Arahkan ke daftar tabel
    }
}
