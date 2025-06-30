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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->mutateFormDataUsing(function (array $data): array {
                    // Mendapatkan tab yang aktif saat ini
                    $activeTab = $this->activeTab ?? 'semua';

                    // Jika tab adalah salah satu lumbung (bukan 'semua'), set nilai default
                    if (str_starts_with($activeTab, 'lumbung_')) {
                        $lumbungCode = strtoupper(str_replace('lumbung_', '', $activeTab));

                        // Mapping kode lumbung ke nama lumbung yang sesuai
                        $lumbungMapping = $this->getLumbungMapping();

                        if (isset($lumbungMapping[$lumbungCode])) {
                            $data['lumbung'] = $lumbungMapping[$lumbungCode];
                        }
                    }

                    return $data;
                }),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'semua' => Tab::make('Semua Data')
                ->badge(LaporanLumbung::count()),
        ];

        // Definisi lumbung A sampai I
        $lumbungList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($lumbungList as $lumbungCode) {
            $count = LaporanLumbung::where('lumbung', $lumbungCode)->count();
            $tabs['lumbung_' . strtolower($lumbungCode)] = Tab::make('LK ' . $lumbungCode)
                ->badge($count)
                ->modifyQueryUsing(function (Builder $query) use ($lumbungCode) {
                    return $query->where('lumbung', $lumbungCode);
                });
        }

        return $tabs;
    }

    /**
     * Mapping kode lumbung ke nama lumbung yang sesuai
     * Sesuaikan dengan data yang ada di database Anda
     */
    protected function getLumbungMapping(): array
    {
        return [
            'A' => 'LUMBUNG A', // Sesuaikan dengan nama yang ada di database
            'B' => 'LUMBUNG B',
            'C' => 'LUMBUNG C',
            'D' => 'LUMBUNG D',
            'E' => 'LUMBUNG E',
            'F' => 'LUMBUNG F',
            'G' => 'LUMBUNG G',
            'H' => 'LUMBUNG H',
            'I' => 'LUMBUNG I',
        ];
    }
}
