<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print | Laporan Lumbung</title>
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

        /* Tanda tangan */
        .signature-container {
            text-align: right;
            margin-top: 20px;
        }

        .signature {
            display: inline-block;
            text-align: center;
            font-size: 0.875rem;
        }

        .signature p {
            margin: 0;
        }

        .sign-box {
            margin-top: 8px;
            height: 64px;
            width: 200px;
            background-color: var(--light-bg);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.75rem;
        }

        .sign-line {
            margin-top: 8px;
            border: 1px solid #000;
            width: 180px;
            margin-left: auto;
            margin-right: auto;
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
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Surat -->
        <header class="header">
            <h1>Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2>Laporan Lumbung</h2>
        </header>

        <div class="divider"></div>

        <!-- Informasi -->
        <section>
            <table class="info-table">
                <tbody>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td>:
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-Y') : 'Tanggal kosong' }}
                        </td>
                        <td class="label">No Laporan</td>
                        <td>: {{ $laporanlumbung->kode }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jam</td>
                        <td>: {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('H:i') : 'Jam kosong' }}</td>
                        <td class="label">Lumbung</td>
                        <td>:
                            {{ $laporanlumbung->lumbung ? ($laporanlumbung->lumbung === 'FIKTIF' ? '' : $laporanlumbung->lumbung) : $laporanlumbung->status_silo }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <div class="divider"></div>
        <div class="caca">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>

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

        <section>
            <div style="overflow-x: auto;">
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
                                            {{ $itemKeluar->data->laporanLumbungMasuk->status_silo ?? $itemKeluar->data->laporanLumbungMasuk->lumbung }}
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
            </div>
        </section>

        <!-- Tanda Tangan (Rata Kanan) -->
        <footer class="signature-container">
            <div class="signature">
                <p>TTD OPERATOR</p>
                <div class="sign-box">
                    <span></span>
                </div>
                <div class="sign-line"></div>
            </div>
        </footer>
    </div>
</body>

</html>