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
            /* border: 2px solid #000000; */
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
            width: 100px;
            color: #000000;
            /* background-color: #f0f0f0; */
        }

        .info-table .value {
            font-weight: bold;
            white-space: nowrap;
            padding-left: 8px;
            min-width: 80px;
        }

        /* Print date */
        .print-date {
            text-align: right;
            font-size: 9pt;
            color: #000000;
            font-style: italic;
            font-weight: bold;
            /* margin: 8px 0; */
            /* border-top: 1.5px solid #000000; */
            /* padding-top: 4px; */
        }

        /* HEADER STYLES */
        .report-header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            /* border: 2px solid #000000; */
            /* background-color: #f0f0f0; */
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
            font-weight: bold;
            font-size: 10pt;
        }

        .detail-table th {
            /* background-color: #e0e0e0; */
            font-weight: bold;
            text-transform: uppercase;
        }

        .detail-table td.text-right {
            text-align: right;
            padding-right: 8px;
        }

        .detail-table .summary-row {
            /* background-color: #f0f0f0; */
            font-weight: bold;
            /* border-top: 1.5px solid #000000; */
        }

        /* SIGNATURE TABLE */
        .signature-table {
            margin-top: 15px;
            /* border: 2px solid #000000; */
        }

        .signature-table th,
        .signature-table td {
            text-align: center;
            /* border: 1px solid #000000; */
            padding: 8px;
        }

        .signature-table th {
            /* background-color: #e0e0e0; */
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

        .text-center {
            text-align: center;
        }

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
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-Y') : 'Tanggal kosong' }}
                        </td>
                        <td class="label">No Laporan</td>
                        <td class="value">
                            : {{ $laporanlumbung->kode }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Jam</td>
                        <td class="value">
                            :
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('h:i') : 'Tanggal kosong' }}
                        </td>
                        <td class="label">Lumbung</td>
                        <td class="value">
                            : {{ $laporanlumbung->dryers->first()->lumbung_tujuan ?? '-' }}
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
            {{-- @php
                $totalKeseluruhan = $laporanlumbung->dryers
                    ->flatMap(fn($dryer) => $dryer->timbangantrontons)
                    ->sum('total_netto');
            @endphp

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>TGL</th>
                        <th>Jenis</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Berat</th>
                        <th>PJ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporanlumbung->dryers as $dryer)
                        @php
                            $count = $dryer->timbangantrontons->count();
                            $rowspan = $count > 0 ? $count : 1;
                            $totalBerat = $dryer->timbangantrontons->sum('total_netto');
                        @endphp

                        @if ($count > 0)
                            @foreach ($dryer->timbangantrontons as $index => $timbangan)
                                <tr>
                                    @if ($index === 0)
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $dryer->created_at ? $dryer->created_at->format('d-m') : '-' }}
                                        </td>
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $dryer->nama_barang }}
                                        </td>
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $dryer->no_dryer }}
                                        </td>
                                    @endif

                                    <td class="center">
                                        {{ $timbangan->kode }}
                                    </td>
                                    <td class="right">
                                        {{ number_format($timbangan->total_netto, 0, ',', '.') }}
                                    </td>

                                    @if ($index === 0)
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $laporanlumbung->user->name }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            {{-- Baris Total Berat per Dryer --}}
            {{-- <tr>
                <td colspan="3"></td>
                <td class="center font-semibold">Total Berat</td>
                <td class="right font-semibold">
                    {{ number_format($totalBerat, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        @else --}}
            {{-- Jika tidak ada data --}}
            {{-- <tr>
                <td class="center">
                    {{ $dryer->created_at ? $dryer->created_at->format('d-m') : '-' }}
                </td>
                <td class="center">{{ $dryer->nama_barang }}</td>
                <td class="center">{{ $dryer->no_dryer }}</td>
                <td class="center" colspan="2">Tidak ada data timbangan tronton</td>
                <td class="center">{{ $laporan_lumbung->user->name }}</td>
            </tr>
            @endif
            @endforeach
            </tbody>
            <tfoot class="table-footer">
                <tr>
                    <td colspan="4" class="center">Total Keseluruhan Berat</td>
                    <td class="right">
                        {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            </table> --}}
{{-- 
            @php
                $dryers = $laporanlumbung->dryers->sortBy('created_at')->values();
                $timbangan = $laporanlumbung->timbangantrontons->sortBy('created_at')->values();
                $max = max($dryers->count(), $timbangan->count());
                $totalKeseluruhan = $laporanlumbung->timbangantrontons->sum('total_netto');
            @endphp

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>TGL</th>
                        <th>Jenis</th>
                        <th>Masuk</th>
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
                        @endphp
                        <tr>
                            <td class="center">
                                {{ $dryer ? $dryer->created_at->format('d-m') : '' }}</td>
                            <td class="center">
                                {{ $dryer ? $dryer->nama_barang : '' }}</td>
                            <td class="center">
                                {{ $dryer ? $dryer->no_dryer : '' }}</td>
                            <td class="center">
                                {{ $timbanganItem ? $timbanganItem->kode : '-' }}</td>
                            <td class="right">
                                {{ $timbanganItem ? number_format($timbanganItem->total_netto, 0, ',', '.') : '-' }}
                            </td>
                            <td class="center">
                                @if ($i == 0)
                                    {{ $laporanlumbung->user->name }}
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="center">
                            Total Berat:</td>
                        <td class="right">
                            {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                    </tr>
                </tfoot>
            </table> --}}



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
                $dryers = $laporanlumbung->dryers->sortBy('created_at')->values();
                $timbangan = $laporanlumbung->timbangantrontons->sortBy('created_at')->values();
                $max = max($dryers->count(), $timbangan->count());
                // Hitung total keseluruhan dari filtered netto
                $totalKeseluruhanFiltered = 0;
            @endphp

            <table class="detail-table">
                <thead>
                    <tr >
                        <th>TGL</th>
                        <th>Jenis</th>
                        <th>Masuk</th>
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
                            <td class="center">
                                {{ $dryer ? $dryer->created_at->format('d-m') : '' }}
                            </td>
                            <td class="center">
                                {{ $dryer ? $dryer->nama_barang : '' }}
                            </td>
                            <td class="center">
                                {{ $dryer ? $dryer->no_dryer : '' }}
                            </td>
                            <td class="center">
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
                            <td class="center">
                                @if ($i == 0)
                                    {{ $laporanlumbung->user->name }}
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr >
                        <td colspan="4" >
                            Total Berat:
                        </td>
                        <td class="text-right">
                            {{ number_format($totalKeseluruhanFiltered, 0, ',', '.') }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>

</html>
