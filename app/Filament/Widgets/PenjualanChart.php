<?php

namespace App\Filament\Widgets;

use App\Models\Penjualan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Gate;

class PenjualanChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan Per Bulan';

    protected function getData(): array
    {
       // Ambil jumlah transaksi per bulan berdasarkan created_at
       $data = Penjualan::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
       ->whereYear('created_at', date('Y')) // Hanya ambil data tahun ini
       ->groupBy('bulan')
       ->orderBy('bulan')
       ->pluck('total', 'bulan')
       ->toArray();

   // Inisialisasi array kosong untuk semua bulan (Jan - Des)
   $penjualanPerBulan = array_fill(1, 12, 0);

   // Isi array dengan hasil query
   foreach ($data as $bulan => $total) {
       $penjualanPerBulan[$bulan] = $total; // Masukkan jumlah transaksi
   }

   return [
       'datasets' => [
           [
               'label' => 'Jumlah Penjualan',
               'data' => array_values($penjualanPerBulan), // Data diurutkan dari Jan - Des
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
    public static function canView(): bool
    {
        return Gate::allows('view_statistik_widget'); // Sesuaikan dengan permission yang Anda buat
    }
}
