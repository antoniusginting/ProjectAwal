<?php

namespace App\Filament\Exports;

use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use App\Models\Sortiran;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SortiranExporter extends Exporter
{
    protected static ?string $model = Sortiran::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('no_sortiran'),
            ExportColumn::make('pembelian.no_spb'),
            ExportColumn::make('kapasitaslumbungbasah.no_kapasitas_lumbung')->label('Lumbung'),
            ExportColumn::make('total_karung'),
            ExportColumn::make('berat_tungkul'),
            ExportColumn::make('kadar_air'),
            ExportColumn::make('netto_bersih'),
            ExportColumn::make('kualitas_jagung_1'),
            ExportColumn::make('x1_x10_1'),
            ExportColumn::make('jumlah_karung_1'),
            ExportColumn::make('tonase_1'),
            ExportColumn::make('kualitas_jagung_2'),
            ExportColumn::make('x1_x10_2'),
            ExportColumn::make('jumlah_karung_2'),
            ExportColumn::make('tonase_2'),
            ExportColumn::make('kualitas_jagung_3'),
            ExportColumn::make('x1_x10_3'),
            ExportColumn::make('jumlah_karung_3'),
            ExportColumn::make('tonase_3'),
            ExportColumn::make('kualitas_jagung_4'),
            ExportColumn::make('x1_x10_4'),
            ExportColumn::make('jumlah_karung_4'),
            ExportColumn::make('tonase_4'),
            ExportColumn::make('kualitas_jagung_5'),
            ExportColumn::make('x1_x10_5'),
            ExportColumn::make('jumlah_karung_5'),
            ExportColumn::make('tonase_5'),
            ExportColumn::make('kualitas_jagung_6'),
            ExportColumn::make('x1_x10_6'),
            ExportColumn::make('jumlah_karung_6'),
            ExportColumn::make('tonase_6'),
            ExportColumn::make('keterangan'),
            ExportColumn::make('user.name'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sortiran export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
