<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;

use App\Models\SuratJalan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SuratJalanExporter extends Exporter
{
    protected static ?string $model = SuratJalan::class;
    private static int $counter = 0;
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nomor')
                ->label('No')
                ->state(function () {
                    return ++self::$counter;
                }),
            ExportColumn::make('kontrak.nama')->label('Nama Kontrak'),
            ExportColumn::make('kontrak2.nama')->label('Kepada Yth.'),
            ExportColumn::make('alamat.alamat'),
            ExportColumn::make('kota'),
            ExportColumn::make('po'),
            ExportColumn::make('jenis_mobil'),
            ExportColumn::make('tambah_berat'),
            ExportColumn::make('tronton.kode')->label('No Penjualan'),
            ExportColumn::make('tronton.penjualan1.tara')->label('Tara'),
            ExportColumn::make('bruto_final')->label('Bruto'),
            ExportColumn::make('netto_final')->label('Netto'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('user.name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your surat jalan export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
