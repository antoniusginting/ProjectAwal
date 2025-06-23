<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print | Laporan Lumbung</title>
    <style>
        /* RESET & BASE STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11pt;
            font-weight: normal;
            line-height: 1.4;
            color: #000;
            background: white;
            padding: 8mm;
        }

        /* CONTAINER */
        .container {
            background-color: white;
            max-width: 100%;
            margin: 0 auto;
        }

        /* TABLE STYLES */
        .table-wrapper {
            overflow-x: visible;
            max-width: 100%;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-weight: bold;
            text-align: left;
        }

        /* Info Table Styles */
        .info-table {
            margin-bottom: 12px;
            
            border: 1px solid #000;
        }

        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
            border: 1px solid #000;
            font-size: 10pt;
        }

        .info-table .label {
            font-weight: bold;
            white-space: nowrap;
            width: 160px;
            background-color: #ffffff;
        }

        .info-table .value {
            font-weight: bold;
            min-width: 20px;
        }

        .info-table .label-right {
            font-weight: bold;
            background-color: #ffffff;
        }

        .info-table .value-right {
            font-weight: bold;
            white-space: nowrap;
            padding-left: 8px;
            width: 160px;
        }

        /* Print date */
        .print-date {
            text-align: right;
            font-size: 9pt;
            font-weight: bold;
            color: #000000;
            font-style: italic;
            margin-bottom: 8px;
        }

        /* HEADER STYLES */
        .report-header {
            text-align: center;
            margin-bottom: 16px;
            padding: 12px 0;
            border-bottom: 3px double #000;
        }

        .report-title {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* DETAIL TABLE */
        .detail-table {
            border: 1px solid #000;
            margin: 12px 0;
            width: 100%;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px 4px;
            font-size: 10pt;
        }

        .detail-table th {
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ffffff;
            border-bottom: 1px solid #000;
        }

        .detail-table td.text-right {
            text-align: right;
            padding-right: 8px;
        }

        .detail-table td.text-center {
            text-align: center;
        }

        .summary-row {
            border-top: 1px solid #000;
            background-color: #ffffff;
        }

        .summary-row td {
            font-weight: bold;
        }

        /* SPB Row Styling */
        .spb-masuk {
            background-color: #ffffff;
        }

        .spb-keluar {
            background-color: #ffffff;
        }

        .spb-label {
            font-weight: bold;
            font-style: italic;
        }

        /* DIVIDER */
        .divider {
            border-bottom: 1px solid #000;
            margin: 8px 0;
        }

        .double-divider {
            border-bottom: 3px double #000;
            margin: 12px 0;
        }

        /* Utility Classes */
        .text-bold {
            font-weight: bold;
        }

        .text-italic {
            font-style: italic;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: white;
                padding: 5mm;
                font-size: 10pt;
                line-height: 1.3;
            }

            .container {
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                padding: 0;
            }

            /* Ensure backgrounds print in black and white */
            .info-table .label,
            .info-table .label-right {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .detail-table th {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-row {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .spb-masuk {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .spb-keluar {
                background-color: #ebebeb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Ensure all borders print */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-date {
                color: #000;
            }
        }

        /* Responsif untuk layar kecil */
        @media screen and (max-width: 768px) {
            body {
                padding: 4mm;
                font-size: 10pt;
            }

            .info-table .label,
            .info-table .value-right {
                width: 120px;
            }

            .report-title {
                font-size: 16pt;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="report-header">
            <h1 class="report-title">Laporan Lumbung</h1>
        </div>

        <!-- Info Table -->
        <table class="info-table">
            <tbody>
                <tr>
                    <td class="label">Tanggal</td>
                    <td class="value">
                        :
                        {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-Y') : 'Tanggal kosong' }}
                    </td>
                    <td class="label-right">No Laporan</td>
                    <td class="value-right">
                        : {{ $laporanlumbung->kode }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Jam</td>
                    <td class="value">
                        : {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('H:i') : 'Jam kosong' }}
                    </td>
                    <td class="label-right">Lumbung</td>
                    <td class="value-right">
                        : {{ $laporanlumbung->lumbung ?? ($laporanlumbung->status_silo ?? '-') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Print Date -->
        <div class="print-date">
            Dicetak: {{ now()->format('d-m-Y H:i:s') }}
        </div>

        <div class="divider"></div>

        <!-- Detail Table -->
        @php
            $lumbungTujuan = $laporanlumbung->lumbung ?? null;
            $dryers = $laporanlumbung->dryers->values();
            $timbangan = $laporanlumbung->timbangantrontons->values();
            $max = max($dryers->count(), $timbangan->count());
            $totalKeseluruhanFiltered = 0;
            $nilai_dryers_sum_total_netto = $dryers->sum('total_netto');
            $totalNettoPenjualansBaru = $laporanlumbung->penjualans->sum('netto') ?? 0;
            $totalGabungan = 0;
        @endphp

        <table class="detail-table">
            <thead>
                <tr>
                    <th>Tgl</th>
                    <th>Jenis</th>
                    <th>Masuk</th>
                    <th>Berat</th>
                    <th>Keluar</th>
                    <th>Berat</th>
                    <th>PJ</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $max; $i++)
                    @php
                        $dryer = $dryers->get($i);
                        $timbanganItem = $timbangan->get($i);
                        $filteredPenjualan = collect();
                        $totalNetto = 0;

                        if ($timbanganItem) {
                            $allPenjualan = collect();
                            $relasiPenjualan = [
                                'penjualan1',
                                'penjualan2',
                                'penjualan3',
                                'penjualan4',
                                'penjualan5',
                                'penjualan6',
                            ];

                            foreach ($relasiPenjualan as $relasi) {
                                if (isset($timbanganItem->$relasi)) {
                                    $dataRelasi = $timbanganItem->$relasi;
                                    if ($dataRelasi instanceof \Illuminate\Database\Eloquent\Collection) {
                                        $allPenjualan = $allPenjualan->merge($dataRelasi);
                                    } elseif ($dataRelasi !== null) {
                                        $allPenjualan->push($dataRelasi);
                                    }
                                }
                            }

                            $filteredPenjualan = $allPenjualan->where('nama_lumbung', $lumbungTujuan);
                            $totalNetto = $filteredPenjualan->sum('netto');
                            $totalKeseluruhanFiltered += $totalNetto;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">
                            {{ $dryer ? $dryer->created_at->format('d-m') : '' }}
                        </td>
                        <td class="text-center">
                            {{ $dryer ? $dryer->nama_barang : '' }}
                        </td>
                        <td class="text-center">
                            {{ $dryer ? $dryer->no_dryer : '' }}
                        </td>
                        <td class="text-right">
                            {{ $dryer && $dryer->total_netto ? number_format($dryer->total_netto, 0, ',', '.') : '' }}
                        </td>
                        <td class="text-center">
                            {{ $timbanganItem ? $timbanganItem->kode : '' }}
                        </td>
                        <td class="text-right">
                            @if ($timbanganItem)
                                @if ($filteredPenjualan->isEmpty())
                                    -
                                @else
                                    {{ number_format($totalNetto, 0, ',', '.') }}
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $timbanganItem ? $timbanganItem->user->name : '' }}
                        </td>
                    </tr>
                @endfor

                <!-- SPB Masuk -->
                @if ($laporanlumbung->penjualans->isNotEmpty())
                    @php
                        $penjualanMasuk = $laporanlumbung->penjualans->filter(function ($penjualan) {
                            return !empty($penjualan->no_spb) && $penjualan->pivot->tipe_penjualan === 'masuk';
                        });

                        $penjualanKeluar = $laporanlumbung->penjualans->filter(function ($penjualan) {
                            return !empty($penjualan->no_spb) && $penjualan->pivot->tipe_penjualan === 'keluar';
                        });
                    @endphp

                    @if ($penjualanMasuk->isNotEmpty())
                        @foreach ($penjualanMasuk as $index => $penjualan)
                            <tr class="spb-masuk">
                                <td colspan="2" class="text-center spb-label">
                                    @if ($index == 0)
                                        SPB Masuk:
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $penjualan->no_spb }}
                                </td>
                                <td class="text-right">
                                    {{ $penjualan->netto ? number_format($penjualan->netto, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-right">-</td>
                                <td class="text-center">
                                    {{ $penjualan->user->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if ($penjualanKeluar->isNotEmpty())
                        @foreach ($penjualanKeluar as $index => $penjualan)
                            <tr class="spb-keluar">
                                <td colspan="2" class="text-center spb-label">
                                    @if ($index == 0)
                                        SPB Keluar:
                                    @endif
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-right">-</td>
                                <td class="text-center">
                                    {{ $penjualan->no_spb }} - {{ $penjualan->silo }}
                                </td>
                                <td class="text-right">
                                    {{ $penjualan->netto ? number_format($penjualan->netto, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $penjualan->user->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endif
            </tbody>

            <tfoot>
                @php
                    $totalGabungan = $totalKeseluruhanFiltered + $totalNettoPenjualansBaru;
                    if ($nilai_dryers_sum_total_netto > 0) {
                        $hasil_pengurangan_numeric_final = ($totalGabungan / $nilai_dryers_sum_total_netto) * 100;
                    } else {
                        $hasil_pengurangan_numeric_final = 0;
                    }
                @endphp
                <tr class="summary-row">
                    <td colspan="3" class="text-center">
                        <strong>Total Berat:</strong>
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($totalNettoPenjualansBaru, 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">-</td>
                    <td class="text-right">
                        @if ($laporanlumbung->lumbung)
                            <strong>{{ number_format($totalGabungan, 0, ',', '.') }}</strong>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($laporanlumbung->lumbung)
                            <strong>{{ number_format($hasil_pengurangan_numeric_final, 2) }}%</strong>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="double-divider"></div>
    </div>
</body>

</html>
