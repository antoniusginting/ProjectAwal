<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use App\Models\Dryer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DryerExporter extends Exporter
{
    protected static ?string $model = Dryer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('no_dryer'),
            ExportColumn::make('kapasitasdryer.nama_kapasitas_dryer')->label('Dryer'),
            ExportColumn::make('laporanLumbung.lumbung')
                ->label('Tujuan')
                ->formatStateUsing(function ($record) {
                    return $record->laporanLumbung?->kode . ' - ' . $record->laporanLumbung?->lumbung;
                    // atau jika field lumbung langsung berisi nama:
                    // return $record->laporanLumbung?->lumbung?->kode . ' - ' . $record->laporanLumbung?->lumbung;
                }),
            ExportColumn::make('operator'),
            ExportColumn::make('nama_barang'),
            ExportColumn::make('rencana_kadar'),
            ExportColumn::make('hasil_kadar'),
            ExportColumn::make('total_netto'),
            ExportColumn::make('pj'),
            // ExportColumn::make('status'),
            ExportColumn::make('no_cc'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your dryer export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
