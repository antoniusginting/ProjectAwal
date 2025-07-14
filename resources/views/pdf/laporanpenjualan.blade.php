<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Laporan Penjualan</title>
    <style>
        /* Definisi variabel untuk konsistensi warna */

        * {
            margin: 4;
            padding: 0;
            box-sizing: border-box;
        }

        /* Gaya dasar halaman dengan ukuran font lebih kecil */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12pt;
            font-weight: bold;
            /* Sekitar 14px */
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header surat dengan ukuran font lebih kecil */
        header.header {
            text-align: center;
            margin-bottom: 12px;
        }

        header.header h1 {
            font-size: 1.4rem;
            margin: 0;
        }

        header.header h2 {
            font-size: 1rem;
            margin: 0;
        }

        /* Divider */
        .divider {
            border-bottom: 1px solid #000;
            /* atau bisa juga 'black' */
            margin: 8px;
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
        }

        .info-table .label {
            font-weight: bold;
        }

        /* Tabel detail pengiriman dengan padding di dalam sel yang lebih kecil */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .detail-table th {
            text-align: center;
        }

        .detail-table td.text-center {
            text-align: center;
        }

        .detail-table td.text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
        }

        /* Tanda tangan */
        .signature-container {
            text-align: right;
            /* Pastikan seluruh konten berada di sisi kanan */
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
            font-size: 0.75rem;
        }

        .sign-line {
            margin-top: 8px;
            border-bottom: 1px solid #000;
            width: 180px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Styles khusus untuk cetak */
        @media print {
            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 12pt;
                margin: 0;
                background-color: var(--secondary-bg);
            }

            .container {
                width: 100%;
                margin: 0;
                padding: 20px;
            }

            .sign-box {
                background-color: #fff;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Surat -->
        <header class="header">
            <h1>Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2>Laporan Penjualan</h2>
        </header>

        <div class="divider"></div>

        <!-- Informasi Pengiriman -->
        <section>
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal</td>
                    <td width="120px">:
                        {{ $laporanpenjualan->created_at->format('d-m-Y') }}
                    <td class="label">Jam</td>
                    <td width="140px">:
                        {{ $laporanpenjualan->created_at->format('H:i') }}
                    </td>
                    <td class="label" width="120px">No Penjualan</td>
                    <td>: {{ $laporanpenjualan->kode }}</td>
                </tr>
                <tr>
                    <td class="label">Operator</td>
                    <td>:
                        {{ $laporanpenjualan->user->name }}
                    </td>
                    <td class="label">
                        {{ !empty($laporanpenjualan->penjualan1->plat_polisi) ? 'Plat Polisi' : 'No Container' }}</td>
                    <td>:
                        {{ $laporanpenjualan->penjualan1->plat_polisi ?? ($laporanpenjualan->penjualanAntarPulau1->no_container ?? '') }}
                    </td>
                </tr>
            </table>
        </section>

        <div class="divider"></div>
        <div class="caca">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>

        <!-- Detail Pengiriman -->
        <section>
            <div style="overflow-x: auto;">
                @if (!empty($laporanpenjualan->penjualan1->plat_polisi))
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No_SPB</th>
                                <th>Jenis</th>
                                <th>Lumbung</th>
                                <th>No Lumbung/IO</th>
                                <th>Satuan Muatan</th> <!-- Kolom untuk jumlah karung / GONI -->
                                <th>Berat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalNetto = 0; @endphp
                            @php $totalKarung = 0; @endphp
                            @php $adaGoni = false; @endphp <!-- Flag untuk mengecek apakah ada GONI -->

                            @for ($i = 1; $i <= 6; $i++)
                                @php $penjualan = $laporanpenjualan->{'penjualan' . $i} ?? null; @endphp
                                <tr>
                                    <td class="text-center">{{ $i }}</td>
                                    <td class="text-center">{{ $penjualan->no_spb ?? '' }}</td>
                                    <td class="text-center">{{ $penjualan->nama_barang ?? '' }}</td>
                                    <td class="text-center">{{ $penjualan->nama_lumbung ?? '' }}</td>
                                    <td class="text-center">{{ $penjualan->no_lumbung ?? '' }}</td>
                                    <td class="text-center">
                                        @if ($penjualan && $penjualan->brondolan == 'GONI')
                                            @php
                                                $adaGoni = true;
                                                $totalKarung += $penjualan->jumlah_karung;
                                            @endphp
                                            {{ $penjualan->jumlah_karung }} - {{ $penjualan->brondolan }}
                                        @else
                                            {{ $penjualan->brondolan ?? '' }}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{ $penjualan ? number_format($penjualan->netto, 0, ',', '.') : '' }}</td>
                                </tr>
                                @if ($penjualan)
                                    @php $totalNetto += $penjualan->netto; @endphp
                                @endif
                            @endfor

                            <!-- Baris total keseluruhan -->
                            <tr class="total-row">
                                <td colspan="5" class="text-center">Total</td>
                                <td class="text-center">
                                    @if ($adaGoni)
                                        {{ number_format($totalKarung, 0, ',', '.') }} - GONI
                                    @else
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ ($totalNetto ?? 0) == 0 ? '' : number_format($totalNetto, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                {{-- Tabel untuk No Container --}}
                @if (empty($laporanpenjualan->penjualan1->plat_polisi) && !empty($laporanpenjualan->penjualanAntarPulau1->no_container))
                    @php
                        $totalNetto = 0;
                        $totalKarung = 0;
                        $adaGoni = false;
                    @endphp

                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No_SPB</th>
                                <th>Jenis</th>
                                <th>Kode Segel</th>
                                <th>Berat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 6; $i++)
                                @php $penjualanAntarPulau = $laporanpenjualan->{'penjualanAntarPulau' . $i} ?? null; @endphp
                                <tr>
                                    <td class="text-center">
                                        {{ $i }}
                                    </td>
                                    <td class="text-center">
                                        {{ $penjualanAntarPulau->kode ?? '' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $penjualanAntarPulau->nama_barang ?? '' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $penjualanAntarPulau->kode_segel ?? '' }}
                                    </td>
                                    <td
                                        class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                                        {{ $penjualanAntarPulau ? number_format($penjualanAntarPulau->netto, 0, ',', '.') : '' }}
                                    </td>
                                </tr>
                                @if ($penjualanAntarPulau)
                                    @php $totalNetto += $penjualanAntarPulau->netto; @endphp
                                @endif
                            @endfor

                            <!-- TOTAL -->
                            <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                                <td colspan="4" class="text-center">Total
                                </td>
                                <td class="text-right">
                                    {{ ($totalNetto ?? 0) == 0 ? '' : number_format($totalNetto, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
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
