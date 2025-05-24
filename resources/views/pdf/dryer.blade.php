<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print | Dryer</title>
    <style>
        /* RESET & BASE STYLES */
        * {
            margin-right: 0.3cm;
            margin-left: 0.2cm;
            margin-top: 0.2cm;
            margin-bottom: 0%;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #1a202c;
        }

        /* CONTAINER */
        .container {
            background-color: white;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            margin: 0 auto;
        }

        /* TABLE STYLES */
        .table-wrapper {
            overflow-x: auto;
            max-width: 100%;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
        }

        /* Info Table Styles */
        .info-table {
            border-spacing: 0;
            margin-bottom: 0.5rem;
        }

        .info-table td {
            /* padding: 0.35rem 0.25rem; */
            vertical-align: top;
        }

        .info-table .label {
            font-weight: 600;
            white-space: nowrap;
            width: 140px;
            color: #374151;
            padding-right: 0;
        }

        .info-table .value {
            white-space: nowrap;
            padding-right: 1rem;
            padding-left: 0.1rem;
        }

        .info-table .colon {
            padding-left: 0;
            width: 10px;
        }

        /* HEADER STYLES */
        .report-header {
            text-align: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #374151;
        }

        .report-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            letter-spacing: 0.05em;
        }

        .report-subtitle {
            font-size: 1rem;
            color: #4b5563;
            font-weight: 500;
        }

        /* DETAIL TABLE */
        .detail-table {
            border: 1px solid #d1d5db;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #d1d5db;
            text-align: center;
        }

        .detail-table th {
            background-color: #f3f4f6;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .detail-table tr:hover {
            background-color: #f9fafb;
        }

        .detail-table td.text-right {
            text-align: right;
        }

        .detail-table .summary-row {
            background-color: #e5e7eb;
            font-weight: 700;
        }

        /* SIGNATURE TABLE */
        .signature-table {
            margin-top: 0.5rem;
        }

        .signature-table th,
        .signature-table td {
            text-align: center;
        }

        .signature-box {
            height: 8rem;
            position: relative;
        }

        .signature-line {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 10rem;
            border-bottom: 1px solid black;
        }

        /* DIVIDER */
        .divider {
            border-bottom: 0.5px solid #d1d5db;
            margin: 0.5rem 0;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: white;
            }

            .container {
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                padding: 0;
            }

            .detail-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .detail-table .summary-row {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="report-header">
            <h1 class="report-title">FORM PERINTAH KERJA DRYER</h1>
        </div>
        <!-- Info Pengiriman -->
        <div class="table-wrapper">
            <table class="info-table">
                {{-- @php
                    $allSortirans = collect();
                    $totalBerat = 0;
                    $totalGoni = 0;

                    // Collect sortirans from all lumbungs
                    for ($i = 1; $i <= 4; $i++) {
                        $lumbungProperty = "lumbung{$i}";
                        if (
                            isset($dryer->$lumbungProperty) &&
                            $dryer->$lumbungProperty &&
                            $dryer->$lumbungProperty->sortirans
                        ) {
                            foreach ($dryer->$lumbungProperty->sortirans as $sortiran) {
                                $sortiran->lumbung_name = "Lumbung {$i}";
                                $allSortirans->push($sortiran);

                                // Sanitasi nilai berat: hanya ambil angka dan titik desimal
                                $beratValue = $sortiran->netto_bersih;
                                if (is_string($beratValue)) {
                                    // Hapus ribuan format seperti 1.000 -> 1000
                                    $beratValue = str_replace('.', '', $beratValue);
                                    // Ganti koma desimal dengan titik jika ada
                                    $beratValue = str_replace(',', '.', $beratValue);
                                    // Hapus semua karakter non-numerik kecuali titik desimal
                                    $beratValue = preg_replace('/[^0-9.]/', '', $beratValue);
                                }
                                $beratValue = empty($beratValue) ? 0 : (float) $beratValue;

                                // Untuk goni (integer), cukup pastikan nilainya tidak null
                                $goniValue = $sortiran->total_karung ?? 0;

                                // Add to totals
                                $totalBerat += $beratValue;
                                $totalGoni += $goniValue;
                            }
                        }
                    }
                @endphp --}}
                @php
                    // Hitung total netto bersih dari semua sortirans
                    $grandTotalNetto = $dryer->sortirans->sum(function ($sortiran) {
                        // Hilangkan titik ribuan, lalu konversi
                        $str = str_replace('.', '', $sortiran->netto_bersih);
                        return is_numeric($str) ? (float) $str : 0;
                    });
                @endphp
                <tbody>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="value">: {{ $dryer->created_at->format('d-m-Y') }}</td>
                        <td class="label">Penanggung Jawab</td>
                        <td class="value">: {{ $dryer->pj }}</td>
                        <td class="label">Dryer/Panggangan</td>
                        <td class="value">: {{ $dryer->kapasitasdryer->nama_kapasitas_dryer }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jam</td>
                        <td class="value">: {{ $dryer->created_at->format('H-i-s') }}</td>
                        <td class="label">Rencana Kadar</td>
                        <td class="value">: {{ $dryer->rencana_kadar }}%</td>
                        <td class="label">Kapasitas Dryer</td>
                        <td class="value">: {{ number_format($dryer->kapasitasdryer->kapasitas_total, '0', ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Operator</td>
                        <td class="value">: {{ $dryer->operator }}</td>
                        <td class="label">Hasil Kadar</td>
                        <td class="value">: {{ $dryer->hasil_kadar }}%</td>
                        <td class="label">Kapasitas Terpakai</td>
                        <td class="value">: {{ number_format($grandTotalNetto, '0', ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Barang</td>
                        <td class="value">: {{ $dryer->nama_barang }}</td>
                        <td class="label">No Dryer</td>
                        <td class="value">: {{ $dryer->no_dryer }}</td>
                        <td class="label">Lumbung Tujuan</td>
                        <td class="value">: {{ $dryer->lumbung_tujuan }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="table-wrapper">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>TGL</th>
                        <th>Jenis</th>
                        <th>Goni</th>
                        <th>Berat</th>
                        <th>No SPB</th>
                        <th>Kadar</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedSortirans = $dryer->sortirans->groupBy('id_sortiran');
                    @endphp

                    @foreach ($groupedSortirans as $idSortiran => $sortiransGroup)
                        @php
                            $totalNettoBersih = 0;
                            $totalTotalKarung = 0;
                        @endphp

                        @foreach ($sortiransGroup as $index => $sortiran)
                            <tr>
                                <td>{{ $sortiran->created_at->format('d/m') ?? '-' }}</td>
                                <td>{{ $sortiran->pembelian->nama_barang }}</td>
                                <td>{{ $sortiran->total_karung ?? '-' }}</td>
                                <td class="text-right">{{ $sortiran->netto_bersih ?? '-' }}</td>
                                <td>{{ $sortiran->pembelian->no_spb ?? '-' }}</td>
                                <td>{{ $sortiran->kadar_air ?? '-' }}%</td>
                                @php
                                    // Hapus pemisah ribuan (titik) dari nilai netto_bersih
                                    $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih);

                                    // Cek jika netto_bersih bisa dikonversi menjadi angka setelah penghapusan titik
                                    $nettoBersihValue = is_numeric($nettoBersihStripped)
                                        ? floatval($nettoBersihStripped)
                                        : 0;
                                    $totalNettoBersih += $nettoBersihValue;

                                    // Hapus pemisah ribuan (titik) dari nilai netto_bersih
                                    $totalKarungStripped = str_replace('.', '', $sortiran->total_karung);

                                    // Cek jika netto_bersih bisa dikonversi menjadi angka setelah penghapusan titik
                                    $totalKarungValue = is_numeric($totalKarungStripped)
                                        ? floatval($totalKarungStripped)
                                        : 0;
                                    $totalTotalKarung += $totalKarungValue;
                                @endphp
                            </tr>
                        @endforeach
                        <tr class="summary-row">
                            <td colspan="2"></td>
                            <td>{{ number_format($totalTotalKarung, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($totalNettoBersih, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signature Table -->
        <table class="signature-table">
            <thead>
                <tr>
                    <th style="width:50%;">TTD Operator</th>
                    <th style="width:50%;">TTD Penanggung Jawab</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="signature-box">
                            <div class="signature-line"></div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-box">
                            <div class="signature-line"></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
