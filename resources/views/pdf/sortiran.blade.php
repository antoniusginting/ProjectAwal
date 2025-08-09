<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Sortiran</title>
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

        .print-date {
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
            <h2>Laporan Sortiran</h2>
        </header>

        <div class="divider"></div>

        <!-- Informasi Sortiran -->
        <section>
            <table class="info-table">
                <tr>
                    <td class="label">No SPB</td>
                    <td>: {{ $sortiran->pembelian->no_spb ?? '-' }}</td>
                    <td class="label">Bruto</td>
                    <td>: {{ number_format($sortiran->pembelian->bruto ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Supplier</td>
                    <td>: {{ $sortiran->pembelian->supplier->nama_supplier ?? '-' }}</td>
                    <td class="label">Tara</td>
                    <td>: {{ number_format($sortiran->pembelian->tara ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Plat Polisi</td>
                    <td>: {{ $sortiran->pembelian->plat_polisi ?? '-' }}</td>
                    <td class="label">Netto</td>
                    <td>: {{ number_format($sortiran->pembelian->netto ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Kadar Air</td>
                    <td>: {{ $sortiran->kadar_air ?? '-' }}@if($sortiran->kadar_air)%@endif</td>
                    <td class="label">Timbangan</td>
                    <td>: KE - {{ $sortiran->pembelian->keterangan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal</td>
                    <td>: {{ $sortiran->created_at ? $sortiran->created_at->format('d-m-Y') : '-' }}</td>
                    <td class="label">Lumbung Basah</td>
                    <td>: {{ $sortiran->kapasitaslumbungbasah->no_kapasitas_lumbung ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Berat Tungkul</td>
                    <td>: {{ $sortiran->berat_tungkul ?? '-' }}</td>
                    <td class="label">Netto Bersih</td>
                    <td>: {{ $sortiran->netto_bersih ?? '-' }}</td>
                </tr>
            </table>
        </section>

        <div class="divider"></div>
        <div class="print-date">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>

        <!-- Detail Sortiran -->
        <section>
            <div style="overflow-x: auto;">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Kualitas Jagung</th>
                            <th>Silang Jagung</th>
                            <th>Jumlah Karung</th>
                            <th>Tonase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_karung = 0;
                            $total_tonase = 0;
                        @endphp
                        @for ($i = 1; $i <= 6; $i++)
                            @php
                                $jumlah_karung = $sortiran["jumlah_karung_$i"] ?? 0;
                                $tonase =
                                    $jumlah_karung > 0
                                        ? floatval(str_replace(',', '.', str_replace('.', '', $sortiran["tonase_$i"] ?? '0')))
                                        : 0;
                            @endphp
                            @if ($jumlah_karung == 0)
                                @continue
                            @endif
                            @php
                                $total_karung += $jumlah_karung;
                                $total_tonase += $tonase;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $i }}</td>
                                <td class="text-center">{{ $sortiran["kualitas_jagung_$i"] ?? '-' }}</td>
                                <td class="text-center">{{ $sortiran["x1_x10_$i"] ?? '-' }}</td>
                                <td class="text-center">{{ $jumlah_karung }}</td>
                                <td class="text-center">{{ number_format($tonase, 0, ',', '.') }}</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td colspan="3" class="text-center">Total</td>
                            <td class="text-center">{{ $total_karung }}</td>
                            <td class="text-center">{{ number_format($total_tonase, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</body>

</html>