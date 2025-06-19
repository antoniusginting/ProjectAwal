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
            font-weight: 900;
            line-height: 1.3;
            color: #000000;
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
            text-align: left;
        }

        /* Info Table Styles */
        .info-table {
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            border: 1px solid #000000;
            font-size: 10pt;
        }

        .info-table .label {
            font-weight: bold;
            white-space: nowrap;
            width: 180px;
            color: #000000;
        }

        .info-table .value {
            font-weight: bolder;
            min-width: 20px;
        }

        .info-table .label-right {
            font-weight: bolder;
            color: #000000;
        }

        .info-table .value-right {
            font-weight: bolder;
            white-space: nowrap;
            padding-left: 8px;
            width: 180px;
        }

        /* Print date */
        .print-date {
            text-align: right;
            font-size: 9pt;
            color: #000000;
            font-style: italic;
            font-weight: bold;
        }

        /* HEADER STYLES */
        .report-header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
        }

        .report-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 8px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .report-subtitle {
            font-size: 11pt;
            color: #000000;
            font-weight: normal;
        }

        /* DETAIL TABLE */
        .detail-table {
            border: 1px solid #000000;
            margin: 10px 0;
            width: 100%;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000000;
            text-align: center;
            padding: 6px 4px;
            font-weight: normal;
            font-size: 10pt;
        }

        .detail-table th {
            font-weight: bold;
            text-transform: uppercase;
        }

        .detail-table td.text-right {
            text-align: right;
            font-weight: bolder;
            padding-right: 8px;
        }
        .detail-table td.text-center {
            text-align: center;
            font-weight: bolder;
            padding-right: 8px;
        }

        .detail-table .summary-row {
            font-weight: bolder;
        }

        .detail-table .summary-row td {
            font-weight: bolder;
        }

        /* SPB Row Styling */
        .spb-row {
            background-color: #ffffff;
        }

        .spb-row td {
            background-color: #ffffff;
        }

        /* SIGNATURE TABLE */
        .signature-table {
            margin-top: 15px;
        }

        .signature-table th,
        .signature-table td {
            text-align: center;
            padding: 8px;
        }

        .signature-table th {
            font-weight: bold;
            font-size: 11pt;
        }

        .signature-box {
            height: 60px;
            position: relative;
            padding: 8px;
        }

        .signature-line {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            border-bottom: 2px solid #000000;
        }

        /* DIVIDER */
        .divider {
            border-bottom: 1.5px solid #000000;
            margin: 8px 0;
        }

        /* Utility Classes */
        .text-bold {
            font-weight: bold;
        }

        /* .text-center {
            text-align: center;
        } */

        .border-thick {
            border: 2px solid #000000;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: white;
                padding: 5mm;
                font-size: 10pt;
                line-height: 1.2;
            }

            .container {
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                padding: 0;
            }

            .detail-table th {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .detail-table .summary-row {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .spb-row {
                background-color: #dbeafe !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-table .label {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .report-header {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .signature-table th {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Pastikan border tetap tercetak */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Responsif untuk layar kecil */
        @media screen and (max-width: 768px) {
            body {
                padding: 4mm;
                font-size: 10pt;
            }

            .info-table .label {
                width: 100px;
            }

            .signature-box {
                height: 50px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Info Pengiriman -->
        <div class="table-container">
            <div class="report-header">
                <h2 class="report-title">LAPORAN LUMBUNG</h2>
            </div>
            <table class="info-table">
                <tbody>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="value">
                            :
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-y') : 'Tanggal kosong' }}
                        </td>
                        <td class="label-right">No Laporan</td>
                        <td class="value-right">
                            : {{ $laporanlumbung->kode }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Jam</td>
                        <td class="value">
                            :
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('h:i') : 'Tanggal kosong' }}
                        </td>
                        <td class="label-right">
                            {{ $laporanlumbung->status_silo ? 'Lumbung' : 'Lumbung' }}
                        </td>
                        <td class="value-right">
                            : {{ $laporanlumbung->lumbung ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="table-responsive">
            <div class="print-date">
                Print Date:
                {{ now()->format('d-m-Y H:i:s') }}
            </div>
            <div class="divider"></div>
            @php
                $lumbungTujuan = $laporanlumbung->lumbung ?? null;
            @endphp

            @foreach ($laporanlumbung->timbangantrontons as $timbanganTronton)
                @php
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
                        if (isset($timbanganTronton->$relasi)) {
                            $dataRelasi = $timbanganTronton->$relasi;

                            if ($dataRelasi instanceof \Illuminate\Database\Eloquent\Collection) {
                                $allPenjualan = $allPenjualan->merge($dataRelasi);
                            } elseif ($dataRelasi !== null) {
                                $allPenjualan->push($dataRelasi);
                            }
                        }
                    }

                    $filteredPenjualan = $allPenjualan->where('nama_lumbung', $lumbungTujuan);
                    $totalNetto = $filteredPenjualan->sum('netto');
                @endphp
            @endforeach
            @php
                $lumbungTujuan = $laporanlumbung->lumbung ?? null;
                $dryers = $laporanlumbung->dryers->values();
                $timbangan = $laporanlumbung->timbangantrontons->values();
                $max = max($dryers->count(), $timbangan->count());
                // Hitung total keseluruhan dari filtered netto
                $totalKeseluruhanFiltered = 0;
                $nilai_dryers_sum_total_netto = $dryers->sum('total_netto');

                // Hitung total netto dari relasi penjualans yang baru (di luar loop)
                $totalNettoPenjualansBaru = $laporanlumbung->penjualans->sum('netto') ?? 0;

                // Total gabungan dideklarasikan di sini
                $totalGabungan = 0;
            @endphp

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>TGL</th>
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

                            // Proses untuk mendapatkan filtered penjualan jika ada timbanganItem
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

                                // Tambahkan ke total keseluruhan
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
                </tbody>

                <!-- Baris untuk menampilkan No SPB -->
                @if ($laporanlumbung->penjualans->isNotEmpty())
                    @php
                        // Filter penjualan yang memiliki no_spb
                        $penjualanWithSpb = $laporanlumbung->penjualans->filter(function ($penjualan) {
                            return !empty($penjualan->no_spb);
                        });
                    @endphp

                    @if ($penjualanWithSpb->isNotEmpty())
                        @foreach ($penjualanWithSpb as $index => $penjualan)
                            <tr class="spb-row">
                                <td colspan="3" class="text-center text-bold">
                                    @if ($index == 0)
                                        No SPB Langsir:
                                    @endif
                                </td>
                                <td></td>
                                <td class="text-center">
                                    {{ $penjualan->no_spb }}
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

                @php
                    // Hitung total gabungan setelah loop selesai
                    $totalGabungan = $totalKeseluruhanFiltered + $totalNettoPenjualansBaru;
                @endphp

                <tfoot>
                    <tr class="summary-row">
                        @php
                            // Hitung selisih SETELAH loop selesai dan $totalKeseluruhanFilteredAccumulated sudah final
                            // $hasil_pengurangan_numeric_final = $nilai_dryers_sum_total_netto - $totalKeseluruhanFiltered;

                            // Cek apakah nilai_dryers_sum_total_netto tidak 0 sebelum pembagian
                            if ($nilai_dryers_sum_total_netto > 0) {
                                $hasil_pengurangan_numeric_final =
                                    ($totalGabungan / $nilai_dryers_sum_total_netto) * 100;
                            } else {
                                $hasil_pengurangan_numeric_final = 0; // atau bisa juga 'N/A'
                            }
                        @endphp
                        <td colspan="3" class="text-center">
                            Total Berat:
                        </td>
                        <td class="text-right">
                            {{ number_format($nilai_dryers_sum_total_netto, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            {{ $laporanlumbung->status_silo ?? '-' }}
                        </td>
                        <td class="text-right">
                            {{ number_format($totalGabungan, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            {{ number_format($hasil_pengurangan_numeric_final, 2) }} %
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>

</html>
