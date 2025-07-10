<?php

namespace App\Filament\Resources\SiloResource\Pages;

use Filament\Actions;
use App\Filament\Resources\SiloResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Silo;
use Filament\Resources\Components\Tab;

class ListSilos extends ListRecords
{
    protected static string $resource = SiloResource::class;


    function getTitle(): string
    {
        return 'Daftar Kapasitas Silo';
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
                        $this->showSiloStatusNotification();
                        $this->halt(); // Stop the action
                    }
                })
                ->mutateFormDataUsing(function (array $data): array {
                    // Auto-set nama berdasarkan tab aktif
                    $activeSilo = $this->getActiveSiloName();
                    if ($activeSilo) {
                        $data['nama'] = $activeSilo;
                    }
                    return $data;
                })
                ->url(function () {
                    // Pass parameter nama ke URL create
                    $activeSilo = $this->getActiveSiloName();
                    if ($activeSilo) {
                        return $this->getResource()::getUrl('create', ['nama' => $activeSilo]);
                    }
                    return $this->getResource()::getUrl('create');
                }),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'semua' => Tab::make('Semua Data')
                ->badge(Silo::count())
                ->badgeColor('primary'),
        ];

        // Definisi silo berdasarkan nama (tanpa luar_pulau)
        $siloList = [
            'silo_staffel_a' => 'Silo Staffel A',
            'silo_staffel_b' => 'Silo Staffel B',
            'silo_2500' => 'Silo 2500',
            'silo_1800' => 'Silo 1800'
        ];

        foreach ($siloList as $siloKey => $siloName) {
            // Ambil data terakhir berdasarkan created_at untuk silo ini
            $latestRecord = $this->getLatestSiloRecord($siloKey);

            // Tentukan badge dan icon berdasarkan status data terakhir
            $badgeInfo = $this->getBadgeInfo($latestRecord);

            $tabs[$siloKey] = Tab::make($siloName)
                ->badge($badgeInfo['icon'])
                ->badgeColor($badgeInfo['color'])
                ->modifyQueryUsing(function (Builder $query) use ($siloKey) {
                    return $this->applySiloFilter($query, $siloKey);
                });
        }

        return $tabs;
    }

    /**
     * Mendapatkan record terakhir berdasarkan kategori silo
     */
    private function getLatestSiloRecord(string $siloKey)
    {
        $query = Silo::query();

        switch ($siloKey) {
            case 'silo_staffel_a':
                $query->where('nama', 'like', '%staffel a%');
                break;
            case 'silo_staffel_b':
                $query->where('nama', 'like', '%staffel b%');
                break;
            case 'silo_2500':
                $query->where('nama', 'like', '%2500%');
                break;
            case 'silo_1800':
                $query->where('nama', 'like', '%1800%');
                break;
        }

        return $query->latest('created_at')->first();
    }

    /**
     * Menerapkan filter berdasarkan kategori silo
     */
    private function applySiloFilter(Builder $query, string $siloKey): Builder
    {
        switch ($siloKey) {
            case 'silo_staffel_a':
                return $query->where('nama', 'like', '%staffel a%');
            case 'silo_staffel_b':
                return $query->where('nama', 'like', '%staffel b%');
            case 'silo_2500':
                return $query->where('nama', 'like', '%2500%');
            case 'silo_1800':
                return $query->where('nama', 'like', '%1800%');
            default:
                return $query;
        }
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

        // Sesuaikan dengan field status yang ada di model Silo
        // Misalnya jika ada field 'status' atau 'is_active'
        if (isset($latestRecord->status)) {
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

        // Default jika tidak ada status
        return [
            'icon' => '●',
            'color' => 'info'
        ];
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

        // Untuk tab silo lainnya, cek status record terakhir
        $latestRecord = $this->getLatestSiloRecord($activeTab);

        if (!$latestRecord) {
            return false;
        }

        // Sesuaikan dengan logic status silo Anda
        return isset($latestRecord->status) ? (bool) $latestRecord->status : true;
    }


    /**
     * Menampilkan notifikasi ketika silo masih terbuka
     */
    private function showSiloStatusNotification(): void
    {
        $activeTab = $this->activeTab ?? 'semua';

        $siloNames = [
            'silo_staffel_a' => 'Silo Staffel A',
            'silo_staffel_b' => 'Silo Staffel B',
            'silo_2500' => 'Silo 2500',
            'silo_1800' => 'Silo 1800'
        ];

        $siloName = $siloNames[$activeTab] ?? 'Silo';

        Notification::make()
            ->title('Tidak dapat menambah data')
            ->body("{$siloName} masih dalam status terbuka. Tutup silo terlebih dahulu sebelum menambah data baru.")
            ->danger()
            ->duration(5000)
            ->send();
    }

    /**
     * Mendapatkan nama silo dari tab aktif
     */
    public function getActiveSiloName(): ?string
    {
        $activeTab = $this->activeTab ?? 'semua';

        $siloMapping = [
            'silo_staffel_a' => 'staffel a',
            'silo_staffel_b' => 'staffel b',
            'silo_2500' => '2500',
            'silo_1800' => '1800'
        ];

        return $siloMapping[$activeTab] ?? null;
    }

    /**
     * Override method untuk handle tab changes
     */
    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed');
    }
}
