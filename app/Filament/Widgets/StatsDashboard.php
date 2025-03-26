<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Penjualan;
use App\Models\Sortiran;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Berat Bersih Sortiran Hari Ini', Sortiran::whereDate('created_at', Carbon::today())->sum('netto_bersih'))
                ->description('Total berat bersih dari sortiran hari ini')
                ->color('info'),

            // Stat::make('Penjualan Hari Ini', Penjualan::whereDate('created_at', Carbon::today())->count())
            //     ->description('Total penjualan pada hari ini')
            //     ->color('info'),
        ];
    }

    public static function getNavigationSort(): int
    {
        return 0; // Urutan paling atas
    }
    protected function getColumns(): int
    {
        return 1; // Membagi menjadi 2 kolom untuk menengahkan
    }
}
