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
                    // Auto-set lumbung atau status_silo berdasarkan tab aktif
                    $activeLumbung = $this->getActiveLumbungCode();
                    $activeSilo = $this->getActiveStatusSilo();

                    if ($activeLumbung) {
                        $data['lumbung'] = $activeLumbung;
                    }

                    if ($activeSilo) {
                        $data['status_silo'] = $activeSilo;
                    }

                    return $data;
                })
                ->url(function () {
                    // Pass parameter lumbung atau status_silo ke URL create
                    $activeLumbung = $this->getActiveLumbungCode();
                    $activeSilo = $this->getActiveStatusSilo();

                    $params = [];
                    if ($activeLumbung) {
                        $params['lumbung'] = $activeLumbung;
                    }
                    if ($activeSilo) {
                        $params['status_silo'] = $activeSilo;
                    }

                    return $this->getResource()::getUrl('create', $params);
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

        // $tabs['silo'] = Tab::make('SILO')
        //     // ->badge($siloCount)
        //     ->badgeColor('info')
        //     ->modifyQueryUsing(function (Builder $query) {
        //         return $query->whereNull('lumbung')
        //             ->orWhere('lumbung', '');
        //     });

        // Tab untuk Silo berdasarkan status_silo
        // Periksa nilai yang ada di database untuk debugging
        $distinctStatusSilo = LaporanLumbung::whereNotNull('status_silo')
            ->distinct()
            ->pluck('status_silo')
            ->toArray();

        // Debug: uncomment baris berikut untuk melihat nilai yang ada
        // dd($distinctStatusSilo);

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
        $siloStatusList = [
            'silo staffel a' => 'Silo Staffel A',
            'silo staffel b' => 'Silo Staffel B',
            'silo 2500' => 'Silo 2500',
            'silo 1800' => 'Silo 1800'
        ];

        foreach ($siloStatusList as $statusValue => $tabLabel) {
            // Ambil data terakhir berdasarkan created_at untuk status_silo ini
            $latestRecord = LaporanLumbung::where('status_silo', $statusValue)
                ->latest('created_at')
                ->first();

            // Tentukan badge dan icon berdasarkan status data terakhir
            $badgeInfo = $this->getBadgeInfo($latestRecord);

            $tabs['silo_' . $statusValue] = Tab::make($tabLabel)
                ->badge($badgeInfo['icon'])
                ->badgeColor($badgeInfo['color'])
                ->modifyQueryUsing(function (Builder $query) use ($statusValue) {
                    return $query->where('status_silo', $statusValue);
                });
        }

        // Tab khusus untuk Lumbung Z (FIKTIF)
        $latestRecordZ = LaporanLumbung::where('lumbung', 'FIKTIF')
            ->latest('created_at')
            ->first();

        $badgeInfoZ = $this->getBadgeInfo($latestRecordZ);

        $tabs['lumbung_fiktif'] = Tab::make('FIKTIF')
            ->badge($badgeInfoZ['icon'])
            ->badgeColor($badgeInfoZ['color'])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('lumbung', 'FIKTIF');
            });
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

        // Tab untuk Silo berdasarkan status_silo
        if (str_starts_with($activeTab, 'silo_')) {
            $statusValue = str_replace('silo_', '', $activeTab);

            $latestRecord = LaporanLumbung::where('status_silo', $statusValue)
                ->latest('created_at')
                ->first();

            // Jika belum ada record, bisa menambah data
            if (!$latestRecord) {
                return true;
            }

            // Jika status false (tertutup), tidak bisa menambah data
            // Jika status true (terbuka), bisa menambah data
            return (bool) $latestRecord->status;
        }

        // Tab untuk Lumbung A-I dan Z (FIKTIF)
        if (str_starts_with($activeTab, 'lumbung_')) {
            $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));

            $latestRecord = LaporanLumbung::where('lumbung', $lumbungCode)
                ->latest('created_at')
                ->first();

            // Jika belum ada record, bisa menambah data
            if (!$latestRecord) {
                return true;
            }

            // Jika status false (tertutup), tidak bisa menambah data
            // Jika status true (terbuka), bisa menambah data
            return (bool) $latestRecord->status;
        }

        return true;
    }

    /**
     * Menampilkan notifikasi ketika lumbung atau silo masih terbuka
     */
    private function showLumbungStatusNotification(): void
    {
        $activeTab = $this->activeTab ?? 'semua';

        if (str_starts_with($activeTab, 'lumbung_')) {
            $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));

            // Khusus untuk Lumbung Z, tampilkan nama FIKTIF
            $lumbungName = $lumbungCode === 'FIKTIF' ? 'FIKTIF' : $lumbungCode;

            Notification::make()
                ->title('Tidak dapat menambah data')
                ->body("Lumbung {$lumbungName} masih dalam status terbuka. Tutup lumbung terlebih dahulu sebelum menambah data baru.")
                ->danger()
                ->duration(5000)
                ->send();
        } elseif (str_starts_with($activeTab, 'silo_')) {
            $statusValue = str_replace('silo_', '', $activeTab);
            $siloName = $this->getSiloDisplayName($statusValue);

            Notification::make()
                ->title('Tidak dapat menambah data')
                ->body("Silo {$siloName} masih dalam status terbuka. Tutup silo terlebih dahulu sebelum menambah data baru.")
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    /**
     * Mendapatkan nama display untuk silo berdasarkan status value
     */
    private function getSiloDisplayName(string $statusValue): string
    {
        $siloNames = [
            'silo staffel a' => 'Staffel A',
            'silo staffel b' => 'Staffel B',
            'silo 2500' => '2500',
            'silo 1800' => '1800'
        ];

        return $siloNames[$statusValue] ?? $statusValue;
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
     * Mendapatkan status_silo dari tab aktif
     */
    public function getActiveStatusSilo(): ?string
    {
        $activeTab = $this->activeTab ?? 'semua';

        if (str_starts_with($activeTab, 'silo_')) {
            return str_replace('silo_', '', $activeTab);
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
