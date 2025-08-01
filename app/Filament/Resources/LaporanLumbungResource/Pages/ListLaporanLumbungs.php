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

                    $params = [];
                    if ($activeLumbung) {
                        $params['lumbung'] = $activeLumbung;
                    }

                    return $this->getResource()::getUrl('create', $params);
                }),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        // Debug: Cek semua nilai lumbung yang ada di database
        $existingLumbungs = LaporanLumbung::distinct()->pluck('lumbung')->filter()->toArray();
        // Uncomment baris berikut untuk debugging
        // dd('Existing lumbungs in database:', $existingLumbungs);

        // Tab AWAL (posisi pertama) - untuk data tanpa lumbung/null
        $awalCount = LaporanLumbung::whereNull('lumbung')
            ->orWhere('lumbung', '')
            ->count();

        $tabs['awal'] = Tab::make('TANPA LUMBUNG')
            ->badge($awalCount)
            ->badgeColor('primary')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where(function ($query) {
                    $query->whereNull('lumbung')
                        ->orWhere('lumbung', '');
                })
                    ->orderByRaw('CASE 
                        WHEN lumbung IS NULL OR lumbung = "" THEN 0 
                        ELSE 1 
                    END')
                    ->orderBy('created_at', 'desc');
            });

        // Tab Semua Data
        $tabs['semua'] = Tab::make('Semua Data')
            ->badge(LaporanLumbung::count())
            ->badgeColor('info');

        // Definisi lumbung A sampai I
        $lumbungList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($lumbungList as $lumbungCode) {
            // Hitung jumlah record untuk lumbung ini
            $lumbungCount = LaporanLumbung::where('lumbung', $lumbungCode)->count();

            // Jika tidak ada data, tampilkan count 0
            if ($lumbungCount == 0) {
                $badgeDisplay = '0';
                $badgeColor = 'gray';
            } else {
                // Logika baru: cek apakah ada record dengan status 0
                $hasStatusZero = LaporanLumbung::where('lumbung', $lumbungCode)
                    ->where('status', 0)
                    ->exists();

                if ($hasStatusZero) {
                    // Jika ada record dengan status 0, tampilkan centang
                    $badgeDisplay = '✓';
                    $badgeColor = 'success';
                } else {
                    // Jika semua record memiliki status 1, tampilkan silang
                    $badgeDisplay = '✕';
                    $badgeColor = 'danger';
                }
            }

            $tabs['lumbung_' . strtolower($lumbungCode)] = Tab::make('LK ' . $lumbungCode)
                ->badge($badgeDisplay)
                ->badgeColor($badgeColor)
                ->modifyQueryUsing(function (Builder $query) use ($lumbungCode) {
                    return $query->where('lumbung', $lumbungCode);
                });
        }

        // Tab untuk Silo berdasarkan lumbung (bukan status_silo lagi)
        $siloLumbungList = [
            'SILO STAFFEL A' => 'Silo Staffel A',
            'SILO STAFFEL B' => 'Silo Staffel B',
            'SILO 2500' => 'Silo 2500',
            'SILO 1800' => 'Silo 1800'
        ];

        foreach ($siloLumbungList as $lumbungValue => $tabLabel) {
            // Ambil data terakhir berdasarkan created_at untuk lumbung ini
            $latestRecord = LaporanLumbung::where('lumbung', $lumbungValue)
                ->latest('created_at')
                ->first();

            // Tentukan badge dan icon berdasarkan status data terakhir
            $badgeInfo = $this->getBadgeInfo($latestRecord);

            $tabs['silo_' . strtolower(str_replace(' ', '_', $lumbungValue))] = Tab::make($tabLabel)
                ->badge($badgeInfo['icon'])
                ->badgeColor($badgeInfo['color'])
                ->modifyQueryUsing(function (Builder $query) use ($lumbungValue) {
                    return $query->where('lumbung', $lumbungValue);
                });
        }

        // Tab khusus untuk Lumbung FIKTIF
        $latestRecordFiktif = LaporanLumbung::where('lumbung', 'FIKTIF')
            ->latest('created_at')
            ->first();

        $badgeInfoFiktif = $this->getBadgeInfo($latestRecordFiktif);

        $tabs['lumbung_fiktif'] = Tab::make('FIKTIF')
            ->badge($badgeInfoFiktif['icon'])
            ->badgeColor($badgeInfoFiktif['color'])
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

    private function canCreateRecord(): bool
    {
        $activeTab = $this->activeTab ?? 'awal';

        // Tab "AWAL" selalu dapat menambah data
        if ($activeTab === 'awal') {
            return true;
        }

        // Tab "Semua Data" tidak dapat menambah data
        if ($activeTab === 'semua') {
            return false;
        }

        // Tab untuk Lumbung A-I dan FIKTIF - TIDAK BISA TAMBAH DATA
        if (str_starts_with($activeTab, 'lumbung_')) {
            return false;
        }

        // Tab untuk Silo - menggunakan logika yang sama dengan lumbung
        if (str_starts_with($activeTab, 'silo_')) {
            $lumbungValue = $this->getSiloLumbungValue($activeTab);

            $latestRecord = LaporanLumbung::where('lumbung', $lumbungValue)
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

        return false;
    }

    /**
     * Mendapatkan nilai lumbung untuk silo berdasarkan tab
     */
    private function getSiloLumbungValue(string $activeTab): string
    {
        $siloMapping = [
            'silo_silo_staffel_a' => 'SILO STAFFEL A',
            'silo_silo_staffel_b' => 'SILO STAFFEL B',
            'silo_silo_2500' => 'SILO 2500',
            'silo_silo_1800' => 'SILO 1800'
        ];

        return $siloMapping[$activeTab] ?? '';
    }

    /**
     * Menampilkan notifikasi ketika tidak bisa menambah data
     */
    private function showLumbungStatusNotification(): void
    {
        $activeTab = $this->activeTab ?? 'awal';

        if ($activeTab === 'semua') {
            Notification::make()
                ->title('Tidak dapat menambah data')
                ->body("Tidak dapat menambah data di tab Semua Data. Pilih tab yang sesuai.")
                ->danger()
                ->duration(5000)
                ->send();
        } elseif (str_starts_with($activeTab, 'lumbung_')) {
            $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));
            $lumbungName = $lumbungCode === 'FIKTIF' ? 'FIKTIF' : $lumbungCode;

            Notification::make()
                ->title('Tidak dapat menambah data')
                ->body("Tidak dapat menambah data di tab Lumbung {$lumbungName}. Gunakan tab AWAL untuk menambah data baru.")
                ->danger()
                ->duration(5000)
                ->send();
        } elseif (str_starts_with($activeTab, 'silo_')) {
            $lumbungValue = $this->getSiloLumbungValue($activeTab);
            $siloName = $this->getSiloDisplayName($lumbungValue);

            Notification::make()
                ->title('Tidak dapat menambah data')
                ->body("Silo {$siloName} masih dalam status terbuka. Tutup silo terlebih dahulu sebelum menambah data baru.")
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    /**
     * Mendapatkan nama display untuk silo berdasarkan lumbung value
     */
    private function getSiloDisplayName(string $lumbungValue): string
    {
        $siloNames = [
            'SILO STAFFEL A' => 'Staffel A',
            'SILO STAFFEL B' => 'Staffel B',
            'SILO 2500' => '2500',
            'SILO 1800' => '1800'
        ];

        return $siloNames[$lumbungValue] ?? $lumbungValue;
    }

    /**
     * Mendapatkan lumbung code dari tab aktif
     */
    public function getActiveLumbungCode(): ?string
    {
        $activeTab = $this->activeTab ?? 'awal';

        // Untuk tab AWAL, return null agar lumbung tidak diset
        if ($activeTab === 'awal') {
            return null;
        }

        // Untuk tab lumbung A-I dan FIKTIF
        if (str_starts_with($activeTab, 'lumbung_')) {
            return strtoupper(str_replace('lumbung_', '', $activeTab));
        }

        // Untuk tab silo, return nilai lumbung yang sesuai
        if (str_starts_with($activeTab, 'silo_')) {
            return $this->getSiloLumbungValue($activeTab);
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
