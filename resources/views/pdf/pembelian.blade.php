<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Pembelian</title>
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
            font-size: 12pt;
            font-weight: bold;
            /* Tambahkan ini supaya bold */
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
            text-align: left;
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
            background-color: var(--light-bg);
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
                /* Hilangkan auto */
                padding: 20px 20px 20px 5px !important;
                /* top, right, bottom, left */
                box-sizing: border-box;
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
