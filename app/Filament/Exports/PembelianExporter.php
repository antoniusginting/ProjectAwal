<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;


use App\Models\Pembelian;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PembelianExporter extends Exporter
{
    protected static ?string $model = Pembelian::class;

    public static function getColumns(): array
    {
        return [
            // ExportColumn::make('id')
            //     ->label('ID'),
            // ExportColumn::make('jenis'),
            ExportColumn::make('no_spb'),
            ExportColumn::make('nama_supir'),
            ExportColumn::make('nama_barang'),
            ExportColumn::make('no_container'),
            ExportColumn::make('brondolan'),
            ExportColumn::make('plat_polisi'),
            ExportColumn::make('bruto'),
            ExportColumn::make('tara'),
            ExportColumn::make('netto'),
            ExportColumn::make('keterangan')->label('Timbangan ke')
                ->prefix('Timbangan ke-'),
            ExportColumn::make('jam_masuk'),
            ExportColumn::make('jam_keluar'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('supplier.nama_supplier'),
            ExportColumn::make('jumlah_karung'),
            ExportColumn::make('user.name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pembelian export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
