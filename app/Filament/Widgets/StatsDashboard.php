<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Penjualan;
use App\Models\Sortiran;
use App\Models\SuratJalan;
use App\Models\TimbanganTronton;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Berat Bersih Sortiran Hari Ini', Sortiran::whereDate('created_at', Carbon::today())->sum('netto_bersih'))
                ->description('Total berat bersih dari sortiran hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Total netto penjualan Hari Ini', number_format(TimbanganTronton::whereDate('created_at', Carbon::today())->sum('netto_final'), 0, ',', '.'))
                ->description('Total netto penjualan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

        ];
    }

    protected static bool $isLazy = false;
    public static function getNavigationSort(): int
    {
        return 0; // Urutan paling atas
    }
    protected function getColumns(): int
    {
        return 2; // Membagi menjadi 2 kolom untuk menengahkan
    }
}
