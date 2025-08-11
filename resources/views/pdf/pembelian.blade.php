<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Pembelian</title>
    <style>
        /* Definisi variabel untuk konsistensi warna */
        :root {
            --primary-color: #000000;
            --secondary-bg: #ffffff;
            --border-color: #000000;
            --light-bg: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Gaya dasar halaman - optimized untuk dot matrix */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt; /* Menggunakan ukuran dari template penjualan */
            font-weight: bold;
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            margin: 15px; /* Diseragamkan dengan template penjualan */
            line-height: 1.2; /* Optimized untuk dot matrix */
        }

        .container {
            max-width: 650px; /* Diseragamkan dengan template penjualan */
            margin: 0 auto;
        }

        /* Header surat dengan ukuran font diseragamkan */
        header.header {
            text-align: left;
            margin-bottom: 10px; /* Diseragamkan dengan template penjualan */
        }

        header.header h1 {
            font-size: 1.2rem; /* Diseragamkan dengan template penjualan */
            margin: 0;
        }

        header.header h2 {
            font-size: 0.9rem; /* Diseragamkan dengan template penjualan */
            margin: 0;
        }

        /* Divider */
        .divider {
            border-bottom: 1px solid #000;
            margin: 6px 0; /* Diseragamkan dengan template penjualan */
        }

        /* Tabel informasi dengan padding diseragamkan */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px; /* Diseragamkan dengan template penjualan */
        }

        .info-table td {
            padding: 1.5px; /* Diseragamkan dengan template penjualan */
            vertical-align: top;
            font-size: 9pt; /* Diseragamkan dengan template penjualan */
        }

        .caca {
            text-align: right;
            margin-bottom: 6px; /* Diseragamkan dengan template penjualan */
            font-size: 8pt; /* Diseragamkan dengan template penjualan */
        }

        .info-table .label {
            font-weight: bold;
        }

        /* Tabel detail pengiriman - optimized untuk dot matrix */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 1px 0.5px; /* Diseragamkan dengan template penjualan */
            font-size: 8pt; /* Diseragamkan dengan template penjualan */
            line-height: 1.1; /* Diseragamkan dengan template penjualan */
        }

        .detail-table th {
            text-align: center;
            background-color: #f5f5f5; /* Sama dengan template penjualan */
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

        /* Tanda tangan - diseragamkan dengan template penjualan */
        .signature-container {
            text-align: right;
            margin-top: 15px; /* Diseragamkan dengan template penjualan */
        }

        .signature {
            display: inline-block;
            text-align: center;
            font-size: 0.8rem; /* Diseragamkan dengan template penjualan */
        }

        .signature p {
            margin: 0;
        }

        .sign-box {
            margin-top: 6px; /* Diseragamkan dengan template penjualan */
            height: 55px; /* Diseragamkan dengan template penjualan */
            width: 180px; /* Diseragamkan dengan template penjualan */
            background-color: var(--light-bg);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.7rem; /* Diseragamkan dengan template penjualan */
        }

        .sign-line {
            margin-top: 6px; /* Diseragamkan dengan template penjualan */
            border: 1px solid #000;
            width: 160px; /* Diseragamkan dengan template penjualan */
            margin-left: auto;
            margin-right: auto;
        }

        /* Media print - OPTIMIZED UNTUK DOT MATRIX */
        @media print {
            @page {
                margin: 0;
                size: A4;
            }

            body {
                font-family: 'Courier New', Courier, monospace !important;
                font-size: 10pt !important; /* Konsisten dengan body font */
                font-weight: bold !important;
                margin: 0 !important;
                background-color: white !important;
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 15px 15px 15px 5px !important; /* Diseragamkan dengan template penjualan */
                box-sizing: border-box;
            }

            /* Optimasi untuk dot matrix printer */
            .info-table td {
                font-size: 8pt !important; /* Font lebih kecil saat print - sama dengan penjualan */
                border: none !important; /* Hilangkan border untuk dot matrix */
            }

            .detail-table {
                width: 100% !important;
                border: 1px solid black !important; /* Border tegas untuk dot matrix */
            }

            .detail-table th,
            .detail-table td {
                font-size: 5pt !important; /* Sama dengan template penjualan */
                padding: 0.3px !important; /* Sama dengan template penjualan */
                border: 1px solid black !important;
                background-color: white !important;
            }

            .detail-table th {
                background-color: white !important; /* Hilangkan background color */
            }

            /* Optimasi divider untuk dot matrix */
            .divider {
                border-bottom: 1px solid black !important;
            }

            /* Optimasi signature untuk dot matrix */
            .sign-box {
                border: 1px solid black !important;
                background-color: white !important;
                color: black !important;
            }

            .sign-line {
                border: 1px solid black !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Surat -->
        <header class="header">
            <h1>Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2>Surat Timbangan Pembelian</h2>
        </header>

        <div class="divider"></div>

        <!-- Informasi Pengiriman -->
        <section>
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal</td>
                    <td>: {{ $pembelian->created_at->format('d-m-Y') }}</td>
                    <td class="label">Jam Masuk</td>
                    <td>: {{ $pembelian->jam_masuk }}</td>
                    <td class="label">Container</td>
                    <td>: {{ $pembelian->no_container ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Operator</td>
                    <td>: {{ $pembelian->user->name }}</td>
                    <td class="label">Jam Keluar</td>
                    <td>: {{ $pembelian->jam_keluar }}</td>
                    <td></td>
                    <td></td>
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
                            <td rowspan="3" class="text-center">{{ $pembelian->no_spb }}</td>
                            <td rowspan="3" class="text-center">{{ $pembelian->plat_polisi }}</td>
                            <td rowspan="3" class="text-center">{{ $pembelian->nama_barang }}</td>
                            <td rowspan="3" class="text-center">
                                {{ optional($pembelian->supplier)->nama_supplier ?? '-' }}</td>
                            <td rowspan="3" class="text-center">{{ $pembelian->nama_supir }}</td>
                            <td rowspan="3" class="text-center">
                                @if ($pembelian->brondolan == 'GONI')
                                    @php
                                        $adaGoni = true;
                                    @endphp
                                    {{ $pembelian->jumlah_karung }} - {{ $pembelian->brondolan }}
                                @else
                                    {{ $pembelian->brondolan }}
                                @endif
                            </td>
                            <td rowspan="3" class="text-center">{{ $pembelian->nama_barang }}</td>
                            <td>Bruto</td>
                            <td class="text-right">{{ number_format($pembelian->bruto, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tara</td>
                            <td class="text-right">{{ number_format($pembelian->tara, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Netto</td>
                            <td class="text-right">{{ number_format($pembelian->netto, 0, ',', '.') }}</td>
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