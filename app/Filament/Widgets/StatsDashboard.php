<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Pembelian;
use App\Models\Penjualan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pembelian Hari Ini', Pembelian::whereDate('created_at', Carbon::today())->count())
                ->description('Total pembelian pada hari ini')
                ->color('success'),

            Stat::make('Penjualan Hari Ini', Penjualan::whereDate('created_at', Carbon::today())->count())
                ->description('Total penjualan pada hari ini')
                ->color('info'),
        ];
    }

    public static function getNavigationSort(): int
    {
        return 0; // Urutan paling atas
    }
    protected function getColumns(): int
    {
        return 2; // Membagi menjadi 2 kolom untuk menengahkan
    }
}
