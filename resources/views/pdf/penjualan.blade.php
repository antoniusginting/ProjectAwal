<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan</title>
    <style>
        /* Definisi variabel untuk konsistensi warna */
        :root {
            --primary-color: #1a202c;
            --secondary-bg: #ffffff;
            --border-color: #e2e8f0;
            --light-bg: #f7fafc;
        }

        /* Gaya dasar halaman dengan ukuran font lebih kecil */
        body {
            font-family: Arial, sans-serif;
            font-size: 0.875rem;
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
            border-bottom: 1px solid var(--border-color);
            margin: 12px 0;
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
            border: 1px solid var(--border-color);
            padding: 4px;
        }

        .detail-table th {
            background-color: var(--light-bg);
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
            color: #718096;
            font-size: 0.75rem;
        }

        .sign-line {
            margin-top: 8px;
            border-bottom: 1px solid var(--border-color);
            width: 180px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Styles khusus untuk cetak */
        @media print {
            body {
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
                    <td>: {{ $penjualan->created_at->format('d-m-Y') }}</td>
                    <td class="label">Jam</td>
                    <td>: {{ $penjualan->created_at->format('H:i:s') }}</td>
                    <td class="label">Nama Lumbung</td>
                    <td>: {{ $penjualan->nama_lumbung }}</td>
                </tr>
                <tr>
                    <td class="label">Operator</td>
                    <td>: {{ $penjualan->user->name }}</td>
                    <td class="label">Container</td>
                    <td>: {{ $penjualan->no_container }}</td>
                    <td class="label">No Lumbung</td>
                    <td>: {{ $penjualan->no_lumbung }}</td>
                </tr>
            </table>
        </section>

        <div class="divider"></div>

        <!-- Detail Pengiriman -->
        <section>
            <div style="overflow-x: auto;">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>No_SPB</th>
                            <th>Plat Polisi</th>
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
                            <td rowspan="3" class="text-center">{{ $penjualan->nama_supir }}</td>
                            <td rowspan="3" class="text-center">{{ $penjualan->brondolan }}</td>
                            <td rowspan="3" class="text-center">{{ $penjualan->nama_barang }}</td>
                            <td>Bruto</td>
                            <td>{{ number_format($penjualan->bruto, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tara</td>
                            <td>{{ number_format($penjualan->tara, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Netto</td>
                            <td>{{ number_format($penjualan->netto, 0, ',', '.') }}</td>
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
