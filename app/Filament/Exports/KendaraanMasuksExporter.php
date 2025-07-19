<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use App\Models\KendaraanMasuks;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class KendaraanMasuksExporter extends Exporter
{
    protected static ?string $model = KendaraanMasuks::class;
    private static int $counter = 0;
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nomor')
                ->label('No')
                ->state(function () {
                    return ++self::$counter;
                }),
            ExportColumn::make('nama_sup_per')->label('Nama'),
            ExportColumn::make('status'),
            ExportColumn::make('jenis'),
            ExportColumn::make('nomor_antrian'),
            ExportColumn::make('plat_polisi'),
            ExportColumn::make('nama_barang'),
            ExportColumn::make('jam_masuk'),
            ExportColumn::make('jam_keluar'),
            ExportColumn::make('keterangan'),
            ExportColumn::make('user.name'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your kendaraan masuks export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
