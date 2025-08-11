<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print | Dryer</title>
    <style>
        /* Definisi variabel untuk konsistensi warna */
        :root {
            --primary-color: #1a202c;
            --secondary-bg: #ffffff;
            --border-color: #ffffff;
            --light-bg: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Gaya dasar halaman dengan ukuran font lebih kecil */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            font-weight: bold;
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            margin: 20px;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
        }

        /* Header surat dengan ukuran font lebih kecil */
        header.header {
            text-align: center;
            margin-bottom: 12px;
        }

        header.header h1 {
            font-size: 1.2rem;
            margin: 0;
        }

        header.header h2 {
            font-size: 1rem;
            margin: 0;
        }

        /* Divider */
        .divider {
            border-bottom: 1px solid #000;
            margin: 8px 0;
        }

        /* Tabel informasi dengan padding dikurangi */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 2px;
            vertical-align: top;
        }

        .caca {
            text-align: right;
            margin-bottom: 8px;
        }

        .info-table .label {
            font-weight: bold;
        }

        .info-table .value {
            font-weight: bold;
        }

        /* Tabel detail pengiriman dengan padding di dalam sel yang lebih kecil */
        .detail-table {
            width: 95%;
            border-collapse: collapse;
            margin-bottom: 12px;
            margin-left: auto;
            margin-right: auto;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 1px 0.5px;
            font-size: 8pt;
            line-height: 1.1;
        }

        .detail-table th {
            text-align: center;
            background-color: #f5f5f5;
        }

        .detail-table td.text-center {
            text-align: center;
        }

        .detail-table td.text-right {
            text-align: right;
        }

        .summary-row {
            background-color: var(--light-bg);
            font-weight: bold;
        }

        /* Signature Table */
        .signature-table {
            width: 95%;
            border-collapse: collapse;
            margin: 20px auto 0 auto;
        }

        .signature-table th,
        .signature-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 1px 0.5px;
            font-size: 8pt;
            line-height: 1.1;
        }

        .signature-table th {
            background-color: #f5f5f5;
            font-weight: bold;
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
            border-bottom: 1px solid #000;
        }

        @media print {
            @page {
                margin: 0;
            }

            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 12pt;
                margin: 0 !important;
                background-color: var(--secondary-bg);
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 20px 20px 20px 5px !important;
                box-sizing: border-box;
            }

            .detail-table {
                width: 95% !important;
            }

            .detail-table th,
            .detail-table td {
                font-size: 5pt !important;
                padding: 0.3px !important;
            }

            .signature-table th,
            .signature-table td {
                font-size: 5pt !important;
                padding: 0.3px !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Surat -->
        <header class="header">
            <h1>Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2>Form Perintah Kerja Dryer</h2>
        </header>

        <div class="divider"></div>

        <!-- Info Pengiriman -->
        <section>
            <table class="info-table">
                @php
                    // Hitung total netto bersih dari semua sortirans
                    $grandTotalNetto = $dryer->sortirans->sum(function ($sortiran) {
                        // Hilangkan titik ribuan, lalu konversi
                        $str = str_replace('.', '', $sortiran->netto_bersih);
                        return is_numeric($str) ? (float) $str : 0;
                    });
                    // Jika hasil penjumlahannya adalah 0 (artinya tidak ada yang valid ditambahkan atau totalnya memang nol),
                    // ubah $grandTotalNetto menjadi string kosong.
                    if ($grandTotalNetto == 0) {
                        $grandTotalNetto = '';
                    }
                @endphp
                <tbody>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="value" width='110px'>:@if (!empty($dryer->pj)){{ optional($dryer->created_at)->format('d-m-Y') }}
                            @endif
                        </td>
                        <td class="label">Penanggung Jawab</td>
                        <td class="value">:{{ $dryer->pj }}</td>
                        <td class="label">Dryer/Panggangan</td>
                        <td class="value">:{{ optional($dryer->kapasitasdryer)->nama_kapasitas_dryer }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jam</td>
                        <td class="value">:@if (!empty($dryer->pj)){{ optional($dryer->created_at)->format('h:i:s') }}
                            @endif
                        </td>
                        <td class="label">Rencana Kadar</td>
                        <td class="value">:{{ $dryer->rencana_kadar }}@if ($dryer->rencana_kadar !== null)%
                            @endif
                        </td>
                        <td class="label">Kapasitas Terpakai</td>
                        <td class="value">
                            :{{ $grandTotalNetto == 0 ? '' : number_format($grandTotalNetto, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Operator</td>
                        <td class="value">:{{ $dryer->operator }}</td>
                        <td class="label">Hasil Kadar</td>
                        <td class="value">:{{ $dryer->hasil_kadar }}@if ($dryer->hasil_kadar !== null)%
                            @endif
                        </td>
                        <td class="label">No IO</td>
                        <td class="value">:{{ optional($dryer->laporanLumbung)->kode ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Barang</td>
                        <td class="value">:{{ $dryer->nama_barang }}</td>
                        <td class="label">No Dryer</td>
                        <td class="value">:{{ $dryer->no_dryer }}</td>
                        <td class="label">Lumbung Tujuan</td>
                        <td class="value">:{{ optional($dryer->laporanLumbung)->lumbung ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <div class="divider"></div>
        <div class="caca">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>

        <!-- Tabel Detail Pengiriman -->
        <section>
            <div style="overflow-x: auto;">
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
                                        <td class="text-center">{{ optional($sortiran->created_at)->format('d/m') ?? '-' }}</td>
                                        <td class = "text-center">{{ optional($sortiran->kapasitaslumbungbasah)->no_kapasitas_lumbung ?? '-' }}</td>
                                        <td class = "text-center">{{ optional($sortiran->pembelian)->nama_barang ?? '-' }}</td>
                                        <td class = "text-center">{{ $sortiran->total_karung ?? '-' }}</td>
                                        <td class="text-right">{{ $sortiran->netto_bersih ?? '-' }}</td>
                                        <td class = "text-center">{{ optional($sortiran->pembelian)->no_spb ?? '-' }}</td>
                                        <td class = "text-center">{{ $sortiran->kadar_air ?? '-' }}%</td>
                                        @php
                                            // Hapus pemisah ribuan (titik) dari nilai netto_bersih
                                            $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih ?? '0');

                                            // Cek jika netto_bersih bisa dikonversi menjadi angka setelah penghapusan titik
                                            $nettoBersihValue = is_numeric($nettoBersihStripped)
                                                ? floatval($nettoBersihStripped)
                                                : 0;
                                            $totalNettoBersih += $nettoBersihValue;

                                            // Hapus pemisah ribuan (titik) dari nilai total_karung
                                            $totalKarungStripped = str_replace('.', '', $sortiran->total_karung ?? '0');

                                            // Cek jika total_karung bisa dikonversi menjadi angka setelah penghapusan titik
                                            $totalKarungValue = is_numeric($totalKarungStripped)
                                                ? floatval($totalKarungStripped)
                                                : 0;
                                            $totalTotalKarung += $totalKarungValue;
                                        @endphp
                                    </tr>
                                @endforeach
                                <tr class="summary-row">
                                    <td colspan="3" class="text-center">TOTAL</td>
                                    <td class="text-center">{{ number_format($totalTotalKarung, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($totalNettoBersih, 0, ',', '.') }}</td>
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
        </section>

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