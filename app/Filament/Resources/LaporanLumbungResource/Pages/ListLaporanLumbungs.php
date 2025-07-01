<?php

namespace App\Filament\Resources\LaporanLumbungResource\Pages;

use App\Filament\Resources\LaporanLumbungResource;
use App\Models\LaporanLumbung;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

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
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->visible(fn () => $this->canCreateRecord())
                ->before(function () {
                    // Double check sebelum create untuk keamanan
                    if (!$this->canCreateRecord()) {
                        $this->showLumbungStatusNotification();
                        $this->halt(); // Stop the action
                    }
                }),
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

    /**
     * Mengecek apakah user dapat membuat record baru berdasarkan tab aktif
     */
    private function canCreateRecord(): bool
    {
        $activeTab = $this->activeTab ?? 'semua';
        
        // Jika di tab "Semua Data", allow create
        if ($activeTab === 'semua') {
            return true;
        }

        // Extract lumbung code dari tab name (format: lumbung_a, lumbung_b, etc.)
        if (str_starts_with($activeTab, 'lumbung_')) {
            $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));
            
            // Ambil record terakhir untuk lumbung ini
            $latestRecord = LaporanLumbung::where('lumbung', $lumbungCode)
                ->latest('created_at')
                ->first();

            // Jika tidak ada record sama sekali, allow create
            if (!$latestRecord) {
                return true;
            }

            // Jika status = 1 (tutup), allow create
            // Jika status = 0 (buka), deny create
            return (bool) $latestRecord->status;
        }

        return true;
    }

    /**
     * Menampilkan notifikasi ketika lumbung masih terbuka
     */
    private function showLumbungStatusNotification(): void
    {
        $activeTab = $this->activeTab ?? 'semua';
        
        if (str_starts_with($activeTab, 'lumbung_')) {
            $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));
            
            Notification::make()
                ->title('Tidak dapat menambah data')
                ->body("Lumbung {$lumbungCode} masih dalam status terbuka. Tutup lumbung terlebih dahulu sebelum menambah data baru.")
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    /**
     * Override method untuk handle tab changes dan update create button visibility
     */
    public function updatedActiveTab(): void
    {
        // Refresh halaman atau update state setelah tab berubah
        // Ini akan memicu re-evaluation dari getHeaderActions()
        $this->dispatch('tab-changed');
    }
}