<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Laporan Penjualan</title>
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

        header.header h2 {
            font-size: 1rem;
            margin: 0;
        }

        /* Divider */
        .divider {
            border-bottom: 1px solid #000;
            /* atau bisa juga 'black' */
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
        .caca{
          margin-bottom: 8px;
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
            color: #718096;
            font-size: 0.75rem;
        }

        .sign-line {
            margin-top: 8px;
            border-bottom : 1px solid #000;
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
                    <td>: {{ $laporanpenjualan->created_at->format('d-m-Y') }}</td>
                    <td class="label">Jam</td>
                    <td>: {{ $laporanpenjualan->created_at->format('H:i') }}</td>
                    <td class="label">No Penjualan</td>
                    <td>: {{ $laporanpenjualan->kode }}</td>
                </tr>
                <tr>
                    <td class="label">Operator</td>
                    <td>: {{ $laporanpenjualan->user->name }}</td>
                    <td class="label">Plat Polisi</td>
                    <td>: {{ $laporanpenjualan->penjualan1->plat_polisi }}</td>
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
                            <th>No</th>
                            <th>No_SPB</th>
                            <th>Jenis</th>
                            <th>Satuan Muatan</th>
                            <th>Lumbung</th>
                            <th>No Lumbung/IO</th>
                            <th>Berat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalNetto = 0; @endphp
                        @for ($i = 1; $i <= 6; $i++)
                            @php $penjualan = $laporanpenjualan->{'penjualan' . $i}; @endphp
                            @if ($penjualan)
                                <tr>
                                    <td class="text-center">{{ $i }}</td>
                                    <td class="text-center">{{ $penjualan->no_spb }}</td>
                                    <td class="text-center">{{ $penjualan->nama_barang }}</td>
                                    <td class="text-center">{{ $penjualan->brondolan }}</td>
                                    <td class="text-center">{{ $penjualan->nama_lumbung }}</td>
                                    <td class="text-center">{{ $penjualan->no_lumbung }}</td>
                                    <td class="text-right">{{ number_format($penjualan->netto, 0, ',', '.') }}</td>
                                </tr>
                                @php $totalNetto += $penjualan->netto; @endphp
                            @endif
                        @endfor
                        <!-- Baris total keseluruhan -->
                        <tr class="total-row">
                            <td colspan="6" class="text-center">Total Berat</td>
                            <td class="text-right">{{ number_format($totalNetto, 0, ',', '.') }}</td>
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
