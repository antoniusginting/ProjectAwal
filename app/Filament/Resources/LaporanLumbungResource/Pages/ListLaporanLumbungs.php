<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use App\Filament\Resources\LaporanLumbungResource;
use App\Models\LaporanLumbung;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLaporanLumbungs extends ListRecords
{
    protected static string $resource = LaporanLumbungResource::class;

    function getTitle(): string
    {
        return 'View Lumbung Kering';
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Data'),

        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'semua' => Tab::make('Semua Data')
                ->badge(LaporanLumbung::count())
                ->badgeColor('primary'),
        ];

        // Definisi lumbung A sampai I
        $lumbungList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($lumbungList as $lumbungCode) {
            // Ambil data terakhir berdasarkan created_at untuk lumbung ini
            $latestRecord = LaporanLumbung::where('lumbung', $lumbungCode)
                ->latest('created_at')
                ->first();

            // Tentukan badge dan icon berdasarkan status data terakhir
            $badgeInfo = $this->getBadgeInfo($latestRecord);

            $tabs['lumbung_' . strtolower($lumbungCode)] = Tab::make('LK ' . $lumbungCode)
                ->badge($badgeInfo['icon'])
                ->badgeColor($badgeInfo['color'])
                ->modifyQueryUsing(function (Builder $query) use ($lumbungCode) {
                    return $query->where('lumbung', $lumbungCode);
                });
        }

        return $tabs;
    }

    /**
     * Menentukan icon dan warna badge berdasarkan status data terakhir
     */
    private function getBadgeInfo($latestRecord): array
    {
        if (!$latestRecord) {
            return [
                'icon' => '?',      // Atau '—' atau '∅'
                'color' => 'gray'
            ];
        }

        // true = tutup (danger), false = buka (success)
        if ($latestRecord->status) {
            return [
                'icon' => '✕',     // Icon tutup - bisa juga '🔒' atau '●'
                'color' => 'danger'
            ]; 
        } else {
            return [
                'icon' => '✓',     // Icon buka - bisa juga '🔓' atau '●'  
                'color' => 'success'
            ];
        }
    }
}
