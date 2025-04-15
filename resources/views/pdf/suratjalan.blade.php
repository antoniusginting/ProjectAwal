<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Surat Jalan</title>
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
            text-align: center;
            margin-bottom: 12px;
        }

        header.header h1 {
            font-size: 1.4rem;
            margin: 0;
        }

        .caca{
            text-align: right;
            margin-bottom: 7px;
        }
        header.header h2 {
            font-size: 1rem;
            margin: 0;
        }

        /* Divider */
        .divider {
            border-bottom: 1px solid #000;
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

        .caca{
            margin-bottom: 8px;
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
            color: #718096;
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
            <h1>{{ $suratjalan->kontrak2->nama }}</h1>
            <h2>Surat Jalan Pengiriman</h2>
        </header>

        <div class="divider"></div>
        
        <!-- Informasi Pengiriman -->
        <section>
            <table class="info-table">
                <tr>
                    <td colspan="2" style="width: 20%;">{{ $suratjalan->kota }}, {{ $suratjalan->created_at->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td style="width: 20%; font-weight: bold;">Kepada Yth.</td>
                    <td style="width: 80%;">: {{ $suratjalan->kontrak->nama }}</td>
                </tr>
                <tr>
                    <td style="width: 20%; font-weight: bold;">Alamat</td>
                    <td style="width: 80%;">: {{ $suratjalan->alamat->alamat }}</td>
                </tr>
                @if (!empty($suratjalan->po))
                <tr>
                    <td style="width: 20%; font-weight: bold;">No PO</td>
                    <td style="width: 80%;">: {{ $suratjalan->po }}</td>
                </tr>
                @endif
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
                            <th class="border p-2">
                                @if(!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                    Plat Polisi
                                @else
                                    No Container
                                @endif
                            </th>
                            <th>Nama Supir</th>
                            <th>Satuan Muatan</th>
                            <th>Nama Barang</th>
                            <th colspan="2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="3" class="text-center">
                                @if (!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                    {{ $suratjalan->tronton->penjualan1->plat_polisi }}
                                @else
                                    {{ $suratjalan->tronton->penjualan1->no_container }}
                                @endif
                            </td>
                            <td rowspan="3" class="text-center">{{ $suratjalan->tronton->penjualan1->nama_supir }}</td>
                            <td rowspan="3" class="text-center">{{$suratjalan->satuan_muatan}}</td>
                            <td rowspan="3" class="text-center">JAGUNG KERING SUPER</td>
                            <td>Bruto</td>
                            <td class="caca" >{{ number_format($suratjalan->bruto_final, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Tara</td>
                            <td class="caca">{{ number_format($suratjalan->tronton->tara_awal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Netto</td>
                            <td class="caca">{{ number_format($suratjalan->netto_final, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Tanda Tangan (Rata Kanan) -->
        <footer class="signature-container">
            <div class="signature">
                <p>Diterima Oleh</p>
                <div class="sign-box">
                    <span></span>
                </div>
                <div class="sign-line"></div>
            </div>
        </footer>
    </div>
</body>

</html>
