<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print | Pembelian</title>
    <style>
        /* Font untuk dot matrix - gunakan font yang tersedia atau download font dot matrix */
        @font-face { 
            font-family: dotmatrix; 
            src: url('1979 Dot Matrix Regular.TTF'), url('Consolas'), url('Courier New'); 
        }

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

        /* Gaya dasar halaman - OPTIMIZED untuk dot matrix */
        body {
            font-family: 'dotmatrix', 'Courier New', Courier, monospace;
            font-size: 10pt;
            font-weight: normal; /* Ubah ke normal untuk dot matrix */
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            margin: 15px;
            line-height: 1.1; /* Lebih rapat untuk dot matrix */
        }

        .container {
            max-width: 618px; /* Sesuai standar dot matrix 8.6 inches */
            margin: 0 auto;
            position: relative;
        }

        /* Header surat dengan ukuran font disesuaikan */
        header.header {
            text-align: left;
            margin-bottom: 8px;
        }

        header.header h1 {
            font-size: 12pt; /* Lebih kecil untuk dot matrix */
            margin: 0;
            font-weight: bold;
        }

        header.header h2 {
            font-size: 10pt;
            margin: 0;
            font-weight: normal;
        }

        /* Divider - simplified untuk dot matrix */
        .divider {
            border-bottom: 1px solid #000;
            margin: 4px 0;
        }

        /* Tabel informasi dengan padding disesuaikan */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .info-table td {
            padding: 1px 2px;
            vertical-align: top;
            font-size: 9pt;
            border: none; /* Hilangkan border untuk dot matrix */
        }

        .caca {
            text-align: right;
            margin-bottom: 4px;
            font-size: 8pt;
        }

        .info-table .label {
            font-weight: bold;
            width: 80px; /* Fixed width untuk alignment */
        }

        /* Tabel detail - OPTIMIZED untuk dot matrix */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 2px solid #000; /* Border tebal untuk dot matrix */
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 2px 4px; /* Padding minimal untuk dot matrix */
            font-size: 8pt;
            line-height: 1.0;
            text-align: left;
        }

        .detail-table th {
            text-align: center;
            background-color: #ffffff; /* No background untuk dot matrix */
            font-weight: bold;
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

        /* Tanda tangan - disesuaikan untuk dot matrix */
        .signature-container {
            text-align: right;
            margin-top: 12px;
        }

        .signature {
            display: inline-block;
            text-align: center;
            font-size: 9pt;
        }

        .signature p {
            margin: 2px 0;
        }

        .sign-box {
            margin-top: 4px;
            height: 40px; /* Lebih kecil untuk dot matrix */
            width: 150px;
            background-color: var(--light-bg);
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000000;
            font-size: 8pt;
        }

        .sign-line {
            margin-top: 4px;
            border-bottom: 1px solid #000;
            width: 140px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Media print - FULLY OPTIMIZED UNTUK DOT MATRIX */
        @media print {
            @page {
                margin: 0.2in; /* Minimal margin */
                size: 8.5in 11in; /* Standard continuous form */
            }

            body {
                font-family: 'dotmatrix', 'Courier New', Courier, monospace !important;
                font-size: 9pt !important; /* Optimal size untuk dot matrix */
                font-weight: normal !important;
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
                padding: 0.1in !important;
                box-sizing: border-box;
            }

            /* Header optimasi dot matrix */
            header.header h1 {
                font-size: 11pt !important;
                font-weight: bold !important;
            }

            header.header h2 {
                font-size: 9pt !important;
            }

            /* Info table untuk dot matrix */
            .info-table td {
                font-size: 8pt !important;
                border: none !important;
                padding: 1px !important;
            }

            .info-table .label {
                font-weight: bold !important;
            }

            /* Detail table untuk dot matrix */
            .detail-table {
                width: 100% !important;
                border: 2px solid black !important;
                page-break-inside: avoid !important;
            }

            .detail-table th,
            .detail-table td {
                font-size: 7pt !important;
                padding: 1px 2px !important;
                border: 1px solid black !important;
                background-color: white !important;
                -webkit-print-color-adjust: exact !important;
            }

            .detail-table th {
                background-color: white !important;
                font-weight: bold !important;
            }

            /* Divider untuk dot matrix */
            .divider {
                border-bottom: 1px solid black !important;
                margin: 2px 0 !important;
            }

            /* Print date */
            .caca {
                font-size: 7pt !important;
                margin-bottom: 2px !important;
            }

            /* Signature untuk dot matrix */
            .signature-container {
                margin-top: 8px !important;
                page-break-inside: avoid !important;
            }

            .signature {
                font-size: 8pt !important;
            }

            .sign-box {
                border: 1px solid black !important;
                background-color: white !important;
                color: black !important;
                height: 30px !important;
                width: 120px !important;
                font-size: 7pt !important;
            }

            .sign-line {
                border-bottom: 1px solid black !important;
                width: 100px !important;
                margin-top: 2px !important;
            }

            /* Hide elements yang tidak perlu di print */
            @media print {
                .no-print {
                    display: none !important;
                }
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

    <!-- Script untuk auto print (optional) -->
    <script>
        // Uncomment baris berikut jika ingin auto print saat load
        // window.onload = function() { window.print(); }
        
        // Function untuk print manual
        function printDocument() {
            window.print();
        }
    </script>
</body>

</html>