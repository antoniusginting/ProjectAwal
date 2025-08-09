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
            font-size: 9pt;
            font-weight: bold;
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            margin: 20px;
        }

        .container {
            max-width: 650px; /* Sesuai dengan template terbaru */
            margin: 0 auto;
        }

        /* Header surat dengan ukuran font lebih kecil */
        header.header {
            text-align: left;
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
            width: 95%; /* Diperkecil dari 100% */
            border-collapse: collapse;
            margin-bottom: 12px;
            margin-left: auto;
            margin-right: auto;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 1px 0.5px; /* Sesuai template */
            font-size: 8pt; /* Diperbaiki ke 8pt sesuai template */
            line-height: 1.1; /* Mengurangi tinggi baris */
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
            <h2>Surat Timbangan Pembelian</h2>
        </header>

        <div class="divider"></div>

        <!-- Informasi Pengiriman -->
        <section>
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal</td>
                    <td>: 07-08-2025</td>
                    <td class="label">Jam Masuk</td>
                    <td>: 08:12:51</td>
                    <td class="label">Container</td>
                    <td>: 21W</td>
                </tr>
                <tr>
                    <td class="label">Operator</td>
                    <td>: dev</td>
                    <td class="label">Jam Keluar</td>
                    <td>: 08:13:06</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </section>

        <div class="divider"></div>
        <div class="caca">Print Date : 09-08-2025 09:15:04</div>
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
                            <td rowspan="3" class="text-center">B1111386</td>
                            <td rowspan="3" class="text-center">BK21</td>
                            <td rowspan="3" class="text-center">JAGUNG GORONTALO (RETUR)</td>
                            <td rowspan="3" class="text-center">ADI KARANG ANYER</td>
                            <td rowspan="3" class="text-center">MANTUL</td>
                            <td rowspan="3" class="text-center">100 - GONI</td>
                            <td rowspan="3" class="text-center">JAGUNG GORONTALO (RETUR)</td>
                            <td>Bruto</td>
                            <td class="text-right">10.000</td>
                        </tr>
                        <tr>
                            <td>Tara</td>
                            <td class="text-right">700</td>
                        </tr>
                        <tr>
                            <td>Netto</td>
                            <td class="text-right">9.300</td>
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