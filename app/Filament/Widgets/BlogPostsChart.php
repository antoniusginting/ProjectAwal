<?php

namespace App\Filament\Widgets;

use App\Models\Pembelian;
use App\Models\Penjualan;
use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Pembelian & Penjualan Per Bulan';

    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $currentYear = date('Y');

        // Data Pembelian per bulan
        $dataPembelian = Pembelian::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', $currentYear) // Hanya ambil data tahun ini
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Data Penjualan per bulan
        $dataPenjualan = Penjualan::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', $currentYear) // Hanya ambil data tahun ini
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Inisialisasi array kosong untuk semua bulan (1 - 12)
        $pembelianPerBulan = array_fill(1, 12, 0);
        $penjualanPerBulan = array_fill(1, 12, 0);

        // Isi array dengan hasil query pembelian
        foreach ($dataPembelian as $bulan => $total) {
            $pembelianPerBulan[$bulan] = $total;
        }

        // Isi array dengan hasil query penjualan
        foreach ($dataPenjualan as $bulan => $total) {
            $penjualanPerBulan[$bulan] = $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pembelian',
                    'data' => array_values($pembelianPerBulan), // Data dari Jan ke Des
                    'backgroundColor' => '#D4AF37', // Warna batang untuk pembelian
                    'borderColor' => '#D4AF37',
                ],
                [
                    'label' => 'Penjualan',
                    'data' => array_values($penjualanPerBulan),
                    'backgroundColor' => '#2424a4', // Warna batang untuk penjualan
                    'borderColor' => '#2424a4',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
}
