<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use App\Models\LaporanLumbung;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LaporanLumbungExporter extends Exporter
{
    protected static ?string $model = LaporanLumbung::class;
    private static int $counter = 0;
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nomor')
                ->label('No')
                ->state(function () {
                    return ++self::$counter;
                }),
            ExportColumn::make('kode')->label('No IO'),
            ExportColumn::make('lumbung')
                ->label('Lumbung')
                ->state(
                    fn($record) =>
                    $record?->lumbung ?:
                        $record?->status_silo ?: ''
                ),
            ExportColumn::make('masuk')
                ->label('Masuk')
                ->formatStateUsing(function ($record) {
                    $dryer = null;
                    $transferMasuk = null;

                    // Handle dryers collection
                    if ($record->dryers && is_object($record->dryers) && method_exists($record->dryers, 'first')) {
                        $dryer = $record->dryers->first()?->no_dryer;
                    }

                    // Handle transferMasuk collection
                    if ($record->transferMasuk && is_object($record->transferMasuk) && method_exists($record->transferMasuk, 'first')) {
                        $transferMasuk = $record->transferMasuk->first()?->kode;
                    } elseif ($record->transferMasuk && is_object($record->transferMasuk)) {
                        // Jika bukan collection tapi single object
                        $transferMasuk = $record->transferMasuk->kode ?? null;
                    }

                    return collect([$dryer, $transferMasuk])->filter()->implode(' / ') ?: '';
                }),

            ExportColumn::make('keluar')
                ->label('Keluar')
                ->formatStateUsing(function ($record) {
                    $penjualan = null;
                    $transferKeluar = null;

                    // Handle penjualans collection
                    if ($record->penjualans && is_object($record->penjualans) && method_exists($record->penjualans, 'first')) {
                        $penjualan = $record->penjualans->first()?->no_spb;
                    }

                    // Handle transferKeluar collection
                    if ($record->transferKeluar && is_object($record->transferKeluar) && method_exists($record->transferKeluar, 'first')) {
                        $transferKeluar = $record->transferKeluar->first()?->kode;
                    } elseif ($record->transferKeluar && is_object($record->transferKeluar)) {
                        // Jika bukan collection tapi single object
                        $transferKeluar = $record->transferKeluar->kode ?? null;
                    }

                    return collect([$penjualan, $transferKeluar])->filter()->implode(' / ') ?: '';
                }),
            ExportColumn::make('keterangan'),
            ExportColumn::make('user.name'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your laporan lumbung export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
