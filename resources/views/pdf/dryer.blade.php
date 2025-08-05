<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print | Dryer</title>
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
            /* border: 1px solid #000000; */
            font-size: 10.7pt;
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
            /* white-space: nowrap;  <-- Hapus ini supaya teks bisa wrap */
            padding-left: 8px;
            min-width: 80px;
            word-wrap: break-word;
            /* supaya kata panjang pecah */
            word-break: break-word;
            /* untuk memaksa pemecahan kata */
            max-width: 200px;
            /* batasi lebar kolom agar tidak terlalu lebar */
        }

        /* Print date */
        .print-date {
            text-align: right;
            font-size: 9pt;
            color: #000000;
            font-style: italic;
            font-weight: bold;
            margin: 8px 0;
            border-top: 1.5px solid #000000;
            padding-top: 4px;
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
            font-size: 1.5rem;
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
            margin: 5px 0;
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
        <div class="report-header">
            <h1 class="report-title">FORM PERINTAH KERJA DRYER</h1>
        </div>

        <!-- Info Pengiriman -->
        <div class="table-wrapper">
            <table class="info-table">
                @php
                    // Hitung total netto bersih dari semua sortirans
                    $grandTotalNetto = $dryer->sortirans->sum(function ($sortiran) {
                        // Hilangkan titik ribuan, lalu konversi
                        $str = str_replace('.', '', $sortiran->netto_bersih);
                        return is_numeric($str) ? (float) $str : 0;
                        // Jika hasil penjumlahannya adalah 0 (artinya tidak ada yang valid ditambahkan atau totalnya memang nol),
                        // ubah $grandTotalNetto menjadi string kosong.
                        if ($grandTotalNetto == 0) {
                            $grandTotalNetto = '';
                        }
                    });
                @endphp
                <tbody>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="value" width='110px'>:@if (!empty($dryer->pj)){{ optional($dryer->created_at)->format('d-m-Y') }}@endif
                        </td>
                        <td class="label">Penanggung Jawab</td>
                        <td class="value">:{{ $dryer->pj }}</td>
                        <td class="label">Dryer/Panggangan</td>
                        <td class="value">:{{ $dryer->kapasitasdryer->nama_kapasitas_dryer }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jam</td>
                        <td class="value">:@if (!empty($dryer->pj)){{ optional($dryer->created_at)->format('h:i:s') }}@endif
                        </td>
                        <td class="label">Rencana Kadar</td>
                        <td class="value">:{{ $dryer->rencana_kadar }}@if ($dryer->rencana_kadar !== null)%
                            @endif
                        </td>
                        <td class="label">Kapasitas Dryer</td>
                        <td class="value">
                            :{{ in_array($dryer->kapasitasdryer->id, [7, 8, 9, 10,11]) ? '' : number_format($dryer->kapasitasdryer->kapasitas_total, '0', ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Operator</td>
                        <td class="value">:{{ $dryer->operator }}</td>
                        <td class="label">Hasil Kadar</td>
                        <td class="value">:{{ $dryer->hasil_kadar }}@if ($dryer->hasil_kadar !== null)%
                            @endif
                        </td>
                        <td class="label">Kapasitas Terpakai</td>
                        <td class="value">
                            :{{ $grandTotalNetto == 0 ? '' : number_format($grandTotalNetto, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Barang</td>
                        <td class="value">:{{ $dryer->nama_barang }}</td>
                        <td class="label">No Dryer</td>
                        <td class="value">:{{ $dryer->no_dryer }}</td>
                        <td class="label">Lumbung Tujuan</td>
                        <td class="whitespace-nowrap">: {{ $dryer->laporanLumbung->lumbung }}</td>   
                    </tr>
                    <tr>
                        <td class="label">No IO</td>
                        <td>: {{ $dryer->laporanLumbung->kode ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Print Date -->
        <div class="print-date">
            Print Date: {{ now()->format('d-m-Y H:i:s') }}
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="table-wrapper">
            @if (!empty($dryer->pj))
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">TGL</th>
                            <th style="width: 20%;">LUMBUNG</th>
                            <th style="width: 20%;">JENIS</th>
                            <th style="width: 12%;">GONI</th>
                            <th style="width: 18%;">BERAT</th>
                            <th style="width: 20%;">NO SPB</th>
                            <th style="width: 13%;">KADAR</th>
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
                                    <td>{{ $sortiran->kapasitaslumbungbasah->no_kapasitas_lumbung }}</td>
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
                                <td colspan="3" class="text-bold">TOTAL</td>
                                <td class="text-bold">{{ number_format($totalTotalKarung, 0, ',', '.') }}</td>
                                <td class="text-right text-bold">{{ number_format($totalNettoBersih, 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                {{-- Contoh Tabel Alternatif Sederhana --}}
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>TGL</th>
                            <th>Nama Lumbung</th>
                            <th>Jenis</th>
                            <th>Goni</th>
                            <th>Berat</th>
                            <th>No Timbangan</th>
                            <th>Kadar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td> <span style="color: transparent;">halo</span></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Signature Table -->
        <table class="signature-table">
            <thead>
                <tr>
                    <th style="width:50%;">TTD OPERATOR</th>
                    <th style="width:50%;">TTD PENANGGUNG JAWAB</th>
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
