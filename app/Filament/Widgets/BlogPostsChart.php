<?php

namespace App\Filament\Widgets;

use App\Models\Pembelian;
use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Pembelian Per Bulan';

    protected function getData(): array
    {
        // Ambil jumlah transaksi per bulan berdasarkan created_at
        $data = Pembelian::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', date('Y')) // Hanya ambil data tahun ini
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Inisialisasi array kosong untuk semua bulan (Jan - Des)
        $pembelianPerBulan = array_fill(1, 12, 0);

        // Isi array dengan hasil query
        foreach ($data as $bulan => $total) {
            $pembelianPerBulan[$bulan] = $total; // Masukkan jumlah transaksi
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah pembelian',
                    'data' => array_values($pembelianPerBulan), // Data diurutkan dari Jan - Des
                    'backgroundColor' => '#000080', // Warna batang chart
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
