<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;

use App\Models\TimbanganTronton;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TimbanganTrontonExporter extends Exporter
{
    protected static ?string $model = TimbanganTronton::class;

    // public static function getColumns(): array
    // {
    //     $columns = [
    //         ExportColumn::make('id')
    //             ->label('ID'),
    //     ];

    //     // Tambahkan semua field dari penjualan (timbangan jual) 1-6
    //     for ($i = 1; $i <= 6; $i++) {
    //         $columns = array_merge($columns, [
    //             // ID timbangan jual (original field)
    //             // ExportColumn::make("id_timbangan_jual_{$i}")
    //             //     ->label("ID Timbangan Jual {$i}"),

    //             // Fields dari relasi penjualan (yang berisi data timbangan jual)
    //             ExportColumn::make("penjualan{$i}.no_spb")
    //                 ->label("TJ{$i} - No SPB"),
    //             ExportColumn::make("penjualan{$i}.nama_supir")
    //                 ->label("TJ{$i} - Nama Supir"),
    //             ExportColumn::make("penjualan{$i}.nama_barang")
    //                 ->label("TJ{$i} - Nama Barang"),
    //             ExportColumn::make("penjualan{$i}.no_container")
    //                 ->label("TJ{$i} - No Container"),
    //             ExportColumn::make("penjualan{$i}.brondolan")
    //                 ->label("TJ{$i} - Brondolan"),
    //             ExportColumn::make("penjualan{$i}.plat_polisi")
    //                 ->label("TJ{$i} - Plat Polisi"),
    //             ExportColumn::make("penjualan{$i}.bruto")
    //                 ->label("TJ{$i} - Bruto"),
    //             ExportColumn::make("penjualan{$i}.tara")
    //                 ->label("TJ{$i} - Tara"),
    //             ExportColumn::make("penjualan{$i}.netto")
    //                 ->label("TJ{$i} - Netto"),
    //             ExportColumn::make("penjualan{$i}.keterangan")
    //                 ->label("TJ{$i} - Timbangan ke")
    //                 ->prefix('Timbangan ke-'),
    //             ExportColumn::make("penjualan{$i}.jam_masuk")
    //                 ->label("TJ{$i} - Jam Masuk"),
    //             ExportColumn::make("penjualan{$i}.jam_keluar")
    //                 ->label("TJ{$i} - Jam Keluar"),
    //             ExportColumn::make("penjualan{$i}.created_at")
    //                 ->label("TJ{$i} - Created At"),
    //             ExportColumn::make("penjualan{$i}.updated_at")
    //                 ->label("TJ{$i} - Updated At"),
    //             ExportColumn::make("penjualan{$i}.supplier.nama_supplier")
    //                 ->label("TJ{$i} - Supplier"),
    //             ExportColumn::make("penjualan{$i}.jumlah_karung")
    //                 ->label("TJ{$i} - Jumlah Karung"),
    //             ExportColumn::make("penjualan{$i}.user.name")
    //                 ->label("TJ{$i} - User"),
    //         ]);
    //     }

    //     // Tambahkan field dari TimbanganTronton
    //     $columns = array_merge($columns, [
    //         ExportColumn::make('bruto_akhir'),
    //         ExportColumn::make('total_netto'),
    //         ExportColumn::make('tara_awal'),
    //         ExportColumn::make('keterangan'),
    //         ExportColumn::make('status'),
    //         ExportColumn::make('created_at'),
    //         ExportColumn::make('updated_at'),
    //         ExportColumn::make('user.name')
    //             ->label('User Name'),
    //     ]);

    //     // Tambahkan field dari penjualan antar pulau 1-3
    //     for ($i = 1; $i <= 3; $i++) {
    //         $columns = array_merge($columns, [
    //             ExportColumn::make("penjualanAntarPulau{$i}.kode")
    //                 ->label("PAP{$i} - No SPB"),
    //             ExportColumn::make("penjualanAntarPulau{$i}.kode_segel")
    //                 ->label("PAP{$i} - Nama Supir"),
    //             ExportColumn::make("penjualanAntarPulau{$i}.nama_barang")
    //                 ->label("PAP{$i} - Nama Barang"),
    //             ExportColumn::make("penjualanAntarPulau{$i}.netto")
    //                 ->label("PAP{$i} - No Container"),
    //             ExportColumn::make("penjualanAntarPulau{$i}.no_container")
    //                 ->label("PAP{$i} - Brondolan"),
    //             ExportColumn::make("penjualanAntarPulau{$i}.created_at")
    //                 ->label("PAP{$i} - Created At"),
    //             ExportColumn::make("penjualanAntarPulau{$i}.updated_at")
    //                 ->label("PAP{$i} - Updated At"),
    //         ]);
    //     }

    // return $columns;
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('kode'),
            ExportColumn::make('penjualan1.no_spb')->label('Timbangan 1'),
            ExportColumn::make('penjualan2.no_spb')->label('Timbangan 2'),
            ExportColumn::make('penjualan3.no_spb')->label('Timbangan 3'),
            ExportColumn::make('penjualan4.no_spb')->label('Timbangan 4'),
            ExportColumn::make('penjualan5.no_spb')->label('Timbangan 5'),
            ExportColumn::make('penjualan6.no_spb')->label('Timbangan 6'),
            ExportColumn::make('penjualanAntarPulau1.kode')->label('Antar Pulau 1'),
            ExportColumn::make('penjualanAntarPulau2.kode')->label('Antar Pulau 2'),
            ExportColumn::make('penjualanAntarPulau3.kode')->label('Antar Pulau 3'),
            ExportColumn::make('tara_awal'),
            ExportColumn::make('bruto_akhir'),
            ExportColumn::make('total_netto'),
            ExportColumn::make('keterangan'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('user.name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your timbangan tronton export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontName('Calibri')
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(31, 78, 121))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }
}
