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
                        : {{ $laporanlumbung->lumbung ?? $laporanlumbung->status_silo }}
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
            // Inisialisasi variabel untuk perhitungan
            $dryers = $laporanlumbung->dryers->values();
            $transferMasuk = $laporanlumbung->transferMasuk->values();
            $penjualanFiltered = $laporanlumbung->penjualans->filter(fn($p) => !empty($p->no_spb));
            $transferKeluar = $laporanlumbung->transferKeluar->values();

            // Gabungkan data masuk (dryers + transferMasuk)
            $dataMasuk = collect();
            foreach ($dryers as $dryer) {
                $dataMasuk->push(
                    (object) [
                        'type' => 'dryer',
                        'data' => $dryer,
                        'created_at' => $dryer->created_at,
                    ],
                );
            }
            foreach ($transferMasuk as $transfer) {
                $dataMasuk->push(
                    (object) [
                        'type' => 'transfer_masuk',
                        'data' => $transfer,
                        'created_at' => $transfer->created_at,
                    ],
                );
            }

            // Gabungkan data keluar (penjualan + transferKeluar)
            $dataKeluar = collect();
            foreach ($penjualanFiltered as $penjualan) {
                $dataKeluar->push(
                    (object) [
                        'type' => 'penjualan',
                        'data' => $penjualan,
                        'created_at' => $penjualan->created_at,
                    ],
                );
            }
            foreach ($transferKeluar as $transfer) {
                $dataKeluar->push(
                    (object) [
                        'type' => 'transfer_keluar',
                        'data' => $transfer,
                        'created_at' => $transfer->created_at,
                    ],
                );
            }

            // Tentukan jumlah baris maksimum untuk tabel
            $maxRows = max($dataMasuk->count(), $dataKeluar->count());

            // Variabel untuk tracking total
            $totalNettoPenjualansBaru = $penjualanFiltered->sum('netto');
            $totalTransferKeluar = $transferKeluar->sum('netto');
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
                @for ($i = 0; $i < $maxRows; $i++)
                    @php
                        // Ambil data untuk baris ini
                        $itemMasuk = $dataMasuk->get($i);
                        $itemKeluar = $dataKeluar->get($i);
                    @endphp

                    <tr>
                        {{-- Kolom Tanggal --}}
                        <td class="text-center">
                            {{ $itemMasuk?->created_at->format('d-m') ?: ($itemKeluar?->created_at->format('d-m') ?: '') }}
                        </td>

                        {{-- Kolom Jenis --}}
                        <td class="text-center">
                            @if ($itemMasuk && $itemMasuk->type == 'dryer')
                                {{ $itemMasuk->data->nama_barang }}
                            @elseif ($itemMasuk && $itemMasuk->type == 'transfer_masuk')
                                
                            @endif
                        </td>

                        {{-- Kolom Masuk --}}
                        <td class="text-center">
                            @if ($itemMasuk)
                                @if ($itemMasuk->type == 'dryer')
                                    {{ $itemMasuk->data->no_dryer }}
                                @elseif ($itemMasuk->type == 'transfer_masuk')
                                    {{ $itemMasuk->data->kode ?? 'Transfer' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        {{-- Kolom Berat Masuk --}}
                        <td class="text-right">
                            @if ($itemMasuk)
                                @if ($itemMasuk->type == 'dryer')
                                    {{ $itemMasuk->data->total_netto ? number_format($itemMasuk->data->total_netto, 0, ',', '.') : '' }}
                                @elseif ($itemMasuk->type == 'transfer_masuk')
                                    {{ $itemMasuk->data->netto ? number_format($itemMasuk->data->netto, 0, ',', '.') : '' }}
                                @endif
                            @endif
                        </td>

                        {{-- Kolom Keluar --}}
                        <td class="text-center">
                            @if ($itemKeluar)
                                @if ($itemKeluar->type == 'penjualan')
                                    {{ $itemKeluar->data->no_spb }}
                                    @if ($itemKeluar->data->silo)
                                        - {{ $itemKeluar->data->silo }}
                                    @endif
                                @elseif ($itemKeluar->type == 'transfer_keluar')
                                    {{ $itemKeluar->data->kode }} -
                                    {{ $itemKeluar->data->laporanLumbungMasuk->status_silo ?? '???' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        {{-- Kolom Berat Keluar --}}
                        <td class="text-right">
                            @if ($itemKeluar)
                                @if ($itemKeluar->type == 'penjualan')
                                    {{ $itemKeluar->data->netto ? number_format($itemKeluar->data->netto, 0, ',', '.') : '-' }}
                                @elseif ($itemKeluar->type == 'transfer_keluar')
                                    {{ $itemKeluar->data->netto ? number_format($itemKeluar->data->netto, 0, ',', '.') : '-' }}
                                @endif
                            @endif
                        </td>

                        {{-- Kolom Penanggung Jawab --}}
                        <td class="text-center">
                            @if ($itemKeluar)
                                @if ($itemKeluar->type == 'penjualan')
                                    {{ $itemKeluar->data->user->name ?? '' }}
                                @elseif ($itemKeluar->type == 'transfer_keluar')
                                    {{ $itemKeluar->data->user->name ?? '' }}
                                @endif
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>

            {{-- Footer dengan Total --}}
            @php
                $totalMasuk = $dryers->sum('total_netto') + $transferMasuk->sum('netto');
                $totalKeluar = $totalNettoPenjualansBaru + $totalTransferKeluar;
                $persentaseKeluar = $totalMasuk > 0 ? ($totalKeluar / $totalMasuk) * 100 : 0;
            @endphp

            <tfoot>
                <tr class="summary-row">
                    <td colspan="3" class="text-center">
                        <strong>Total:</strong>
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($totalMasuk, 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">-</td>
                    <td class="text-right">
                        <strong>{{ number_format($totalKeluar, 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">
                        @if ($laporanlumbung->lumbung && $laporanlumbung->status)
                            <strong>{{ number_format($persentaseKeluar, 2) }}%</strong>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="double-divider"></div>
    </div>
</body>

</html>
