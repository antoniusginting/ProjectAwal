<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use App\Models\Transfer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransferExporter extends Exporter
{
    protected static ?string $model = Transfer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('kode'),
            ExportColumn::make('laporanLumbungMasuk.kode')->label('No IO Keluar'),
            ExportColumn::make('laporanLumbungKeluar.kode')->label('No IO Masuk'),
            ExportColumn::make('nama_supir'),
            ExportColumn::make('plat_polisi'),
            ExportColumn::make('nama_barang'),
            ExportColumn::make('bruto'),
            ExportColumn::make('tara'),
            ExportColumn::make('netto'),
            ExportColumn::make('keterangan')->label('Timbangan ke')
                ->prefix('Timbangan ke-'),
            ExportColumn::make('jam_masuk'),
            ExportColumn::make('jam_keluar'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('user.name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transfer export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
