<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Penjualan</title>
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
            font-size: 10pt; /* Diseragamkan dengan template pembelian */
            font-weight: bold;
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            margin: 15px; /* Diseragamkan dengan template pembelian */
        }

        .container {
            max-width: 650px; /* Diseragamkan dengan template pembelian */
            margin: 0 auto;
        }

        /* Header surat dengan ukuran font lebih kecil */
        header.header {
            text-align: left;
            margin-bottom: 10px; /* Diseragamkan dengan template pembelian */
        }

        header.header h1 {
            font-size: 1.2rem; /* Diseragamkan dengan template pembelian */
            margin: 0;
        }

        header.header h2 {
            font-size: 0.9rem; /* Diseragamkan dengan template pembelian */
            margin: 0;
        }

        /* Divider */
        .divider {
            border-bottom: 1px solid #000;
            margin: 6px 0; /* Diseragamkan dengan template pembelian */
        }

        /* Tabel informasi dengan padding dikurangi */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px; /* Diseragamkan dengan template pembelian */
        }

        .info-table td {
            padding: 1.5px; /* Diseragamkan dengan template pembelian */
            vertical-align: top;
            font-size: 9pt; /* Diseragamkan dengan template pembelian */
        }

        .caca {
            text-align: right;
            margin-bottom: 6px; /* Diseragamkan dengan template pembelian */
            font-size: 8pt; /* Diseragamkan dengan template pembelian */
        }

        .info-table .label {
            font-weight: bold;
        }

        /* Tabel detail pengiriman dengan padding di dalam sel yang lebih kecil */
        .detail-table {
            width: 100%; /* Kembali ke 100% untuk luruskan dengan divider */
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 1px 0.5px; /* Diseragamkan dengan template pembelian */
            font-size: 8pt; /* Diseragamkan dengan template pembelian */
            line-height: 1.1; /* Diseragamkan dengan template pembelian */
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

        .total-row {
            background-color: var(--light-bg);
            font-weight: bold;
        }

        /* Tanda tangan */
        .signature-container {
            text-align: right;
            margin-top: 15px; /* Diseragamkan dengan template pembelian */
        }

        .signature {
            display: inline-block;
            text-align: center;
            font-size: 0.8rem; /* Diseragamkan dengan template pembelian */
        }

        .signature p {
            margin: 0;
        }

        .sign-box {
            margin-top: 6px; /* Diseragamkan dengan template pembelian */
            height: 55px; /* Diseragamkan dengan template pembelian */
            width: 180px; /* Diseragamkan dengan template pembelian */
            background-color: var(--light-bg);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.7rem; /* Diseragamkan dengan template pembelian */
        }

        .sign-line {
            margin-top: 6px; /* Diseragamkan dengan template pembelian */
            border: 1px solid #000;
            width: 160px; /* Diseragamkan dengan template pembelian */
            margin-left: auto;
            margin-right: auto;
        }

        @media print {
            @page {
                margin: 0;
            }

            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 10pt; /* Konsisten dengan body font */
                margin: 0 !important;
                background-color: var(--secondary-bg);
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 15px 15px 15px 5px !important; /* Diseragamkan dengan template pembelian */
                box-sizing: border-box;
            }

            .info-table td {
                font-size: 8pt !important; /* Font lebih kecil saat print */
            }

            .detail-table {
                width: 100% !important; /* Konsisten dengan screen */
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
            <h2>Surat Timbangan Penjualan</h2>
        </header>

        <div class="divider"></div>

        <!-- Informasi Pengiriman -->
        <section>
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal</td>
                    <td>: {{ $penjualan->created_at->format('d-m-Y') }}</td>
                    <td class="label">Nama Lumbung</td>
                    <td>: {{ $penjualan->nama_lumbung ?? ''}} {{$penjualan->silos->nama ?? ''}}</td>
                    <td class="label">Container</td>
                    <td>: {{ $penjualan->no_container }}</td>
                </tr>
                <tr>
                    <td class="label">Operator</td>
                    <td>: {{ $penjualan->user->name }}</td>
                    <td class="label">No Lumbung</td>
                    <td>: {{ $penjualan->laporanLumbung->kode ?? ($penjualan->silos->nama ?? '-') }}</td>
                    <td class="label">Jam Masuk</td>
                    <td>: {{ $penjualan->jam_masuk }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="label">Jam Keluar</td>
                    <td>: {{ $penjualan->jam_keluar }}</td>
                </tr>
            </table>
        </section>

        <div class="divider"></div>
        <div class="caca">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>
        <!-- Detail Pengiriman -->
        <section>
            <div style="overflow-x: auto;">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>No_SPB</th>
                            <th>Plat Polisi</th>
                            <th>Nama Barang</th>
                            <th>Nama Supplier</th>
                            <th>Nama Supir</th>
                            <th>Satuan Muatan</th>
                            <th>Nama Barang</th>
                            <th colspan="2">Berat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="3" class="text-center">{{ $penjualan->no_spb }}</td>
                            <td rowspan="3" class="text-center">{{ $penjualan->plat_polisi }}</td>
                            <td rowspan="3" class="text-center">{{ $penjualan->nama_barang }}</td>
                            <td rowspan="3" class="text-center">-</td>
                            <td rowspan="3" class="text-center">{{ $penjualan->nama_supir }}</td>
                            <td rowspan="3" class="text-center">
                                @if ($penjualan->brondolan == 'GONI')
                                    @php
                                        $adaGoni = true;
                                    @endphp
                                    {{ $penjualan->jumlah_karung }} - {{ $penjualan->brondolan }}
                                @else
                                    {{ $penjualan->brondolan }}
                                @endif
                            </td>
                            <td rowspan="3" class="text-center">{{ $penjualan->nama_barang }}</td>
                            <td>Bruto</td>
                            <td class="text-right">{{ number_format($penjualan->bruto, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tara</td>
                            <td class="text-right">{{ number_format($penjualan->tara, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Netto</td>
                            <td class="text-right">{{ number_format($penjualan->netto, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
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