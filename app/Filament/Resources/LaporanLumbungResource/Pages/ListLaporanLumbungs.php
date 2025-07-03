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
                ->visible(fn() => $this->canCreateRecord())
                ->before(function () {
                    // Double check sebelum create untuk keamanan
                    if (!$this->canCreateRecord()) {
                        $this->showLumbungStatusNotification();
                        $this->halt(); // Stop the action
                    }
                })
                ->mutateFormDataUsing(function (array $data): array {
                    // Auto-set lumbung berdasarkan tab aktif
                    $activeLumbung = $this->getActiveLumbungCode();
                    if ($activeLumbung) {
                        $data['lumbung'] = $activeLumbung;
                    }
                    return $data;
                })
                ->url(function () {
                    // Pass parameter lumbung ke URL create
                    $activeLumbung = $this->getActiveLumbungCode();
                    if ($activeLumbung) {
                        return $this->getResource()::getUrl('create', ['lumbung' => $activeLumbung]);
                    }
                    return $this->getResource()::getUrl('create');
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

        // Tab SILO untuk data tanpa nilai lumbung
        $siloCount = LaporanLumbung::whereNull('lumbung')
            ->orWhere('lumbung', '')
            ->count();

        $tabs['silo'] = Tab::make('SILO')
            // ->badge($siloCount)
            ->badgeColor('info')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereNull('lumbung')
                    ->orWhere('lumbung', '');
            });

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
                'icon' => '?',
                'color' => 'gray'
            ];
        }

        if ($latestRecord->status) {
            return [
                'icon' => '✕',
                'color' => 'danger'
            ];
        } else {
            return [
                'icon' => '✓',
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

        // Tab "Semua Data" tidak dapat menambah data
        if ($activeTab === 'semua') {
            return false;
        }

        // Tab SILO selalu dapat menambah data
        if ($activeTab === 'silo') {
            return true;
        }

        if (str_starts_with($activeTab, 'lumbung_')) {
            $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));

            $latestRecord = LaporanLumbung::where('lumbung', $lumbungCode)
                ->latest('created_at')
                ->first();

            if (!$latestRecord) {
                return true;
            }

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
     * Mendapatkan lumbung code dari tab aktif
     */
    public function getActiveLumbungCode(): ?string
    {
        $activeTab = $this->activeTab ?? 'semua';

        // Untuk tab SILO, return null agar lumbung tidak diset
        if ($activeTab === 'silo') {
            return null;
        }

        if (str_starts_with($activeTab, 'lumbung_')) {
            return strtoupper(str_replace('lumbung_', '', $activeTab));
        }

        return null;
    }

    /**
     * Override method untuk handle tab changes
     */
    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed');
    }
}
