<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print | Laporan Lumbung</title>
    <style>
        /* style.css */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Card container */
        .card {
            padding: 24px;
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            color: #111827;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Responsif overflow */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        /* Container untuk tabel */
        .table-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
        }

        /* Styling tabel info */
        .table-title {
            font-size: 22px;
            text-align: center;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fcfcfc;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            overflow: hidden;
        }

        .info-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .info-table td {
            padding: 5px 6px;
            font-size: 14px;
            line-height: 1.4;
            vertical-align: middle;
            border-bottom: 1px solid #eaeaea;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        /* Styling kolom label (kolom pertama dan ketiga) */
        .info-table td:nth-child(1),
        .info-table td:nth-child(3) {
            font-weight: 600;
            color: #444;
            background-color: #f2f2f2;
            width: 20%;
            border-right: 1px solid #eaeaea;
        }

        /* Styling kolom value (kolom kedua dan keempat) */
        .info-table td:nth-child(2),
        .info-table td:nth-child(4) {
            width: 30%;
        }

        /* Membuat ":" tidak terlalu mengganggu */
        .info-table td:nth-child(2),
        .info-table td:nth-child(4) {
            color: #333;
        }

        /* Hover efek untuk baris */
        .info-table tr:hover {
            background-color: #f0f7ff;
        }

        /* Styling judul tabel (jika diperlukan) */
        .table-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        /* Divider */
        .divider {
            border-bottom: 2px solid #D1D5DB;
            margin: 10px 0;
        }

        /* Print date */
        .print-date {
            text-align: right;
            font-size: 14px;
            margin-bottom: 12px;
            color: #6B7280;
            font-style: italic;
        }

        /* Detail table */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #D1D5DB;
            padding: 4px;
            font-size: 14px;
        }

        .detail-table thead tr {
            background-color: #F3F4F6;
        }

        .detail-table th {
            text-align: center;
            font-weight: 600;
            padding: 6px 4px;
            background-color: #E5E7EB;
            color: #374151;
        }

        .detail-table td.center {
            text-align: center;
        }

        .detail-table td.right {
            text-align: right;
        }

        /* Footer total */
        .table-footer tr {
            background-color: #F3F4F6;
            font-weight: 700;
        }

        .table-footer td {
            padding: 6px 4px;
        }
    </style>
</head>

<body>
    <div class="card">
        <!-- Info Pengiriman -->
        <div class="table-container">
            <div class="table-title">
                <h2>LAPORAN LUMBUNG</h2>
            </div>
            <table class="info-table">
                <tbody>
                    <tr>
                        <td>Tanggal</td>
                        <td>
                            :
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-Y') : 'Tanggal kosong' }}
                        </td>
                        <td>No Laporan</td>
                        <td>
                            : {{ $laporanlumbung->kode }}
                        </td>
                    </tr>
                    <tr>
                        <td>Jam</td>
                        <td>
                            :
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('h:i') : 'Tanggal kosong' }}
                        </td>
                        <td>Lumbung</td>
                        <td>
                            : {{ $laporanlumbung->dryers->first()->lumbung_tujuan ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="table-responsive">
            <div class="print-date">
                Print Date:
                {{ now()->format('d-m-Y H:i:s') }}
            </div>

            {{-- @php
                $totalKeseluruhan = $laporanlumbung->dryers
                    ->flatMap(fn($dryer) => $dryer->timbangantrontons)
                    ->sum('total_netto');
            @endphp

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>TGL</th>
                        <th>Jenis</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Berat</th>
                        <th>PJ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporanlumbung->dryers as $dryer)
                        @php
                            $count = $dryer->timbangantrontons->count();
                            $rowspan = $count > 0 ? $count : 1;
                            $totalBerat = $dryer->timbangantrontons->sum('total_netto');
                        @endphp

                        @if ($count > 0)
                            @foreach ($dryer->timbangantrontons as $index => $timbangan)
                                <tr>
                                    @if ($index === 0)
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $dryer->created_at ? $dryer->created_at->format('d-m') : '-' }}
                                        </td>
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $dryer->nama_barang }}
                                        </td>
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $dryer->no_dryer }}
                                        </td>
                                    @endif

                                    <td class="center">
                                        {{ $timbangan->kode }}
                                    </td>
                                    <td class="right">
                                        {{ number_format($timbangan->total_netto, 0, ',', '.') }}
                                    </td>

                                    @if ($index === 0)
                                        <td class="center" rowspan="{{ $rowspan }}">
                                            {{ $laporanlumbung->user->name }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            {{-- Baris Total Berat per Dryer --}}
            {{-- <tr>
                <td colspan="3"></td>
                <td class="center font-semibold">Total Berat</td>
                <td class="right font-semibold">
                    {{ number_format($totalBerat, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        @else --}}
            {{-- Jika tidak ada data --}}
            {{--<tr>
                <td class="center">
                    {{ $dryer->created_at ? $dryer->created_at->format('d-m') : '-' }}
                </td>
                <td class="center">{{ $dryer->nama_barang }}</td>
                <td class="center">{{ $dryer->no_dryer }}</td>
                <td class="center" colspan="2">Tidak ada data timbangan tronton</td>
                <td class="center">{{ $laporan_lumbung->user->name }}</td>
            </tr>
            @endif
            @endforeach
            </tbody>
            <tfoot class="table-footer">
                <tr>
                    <td colspan="4" class="center">Total Keseluruhan Berat</td>
                    <td class="right">
                        {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            </table> --}}

            @php
                $dryers = $laporanlumbung->dryers->sortBy('created_at')->values();
                $timbangan = $laporanlumbung->timbangantrontons->sortBy('created_at')->values();
                $max = max($dryers->count(), $timbangan->count());
                $totalKeseluruhan = $laporanlumbung->timbangantrontons->sum('total_netto');
            @endphp

            <table class="detail-table">
                <thead>
                    <tr>
                        <th>TGL</th>
                        <th>Jenis</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Berat</th>
                        <th>PJ</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < $max; $i++)
                        @php
                            $dryer = $dryers->get($i);
                            $timbanganItem = $timbangan->get($i);
                        @endphp
                        <tr>
                            <td class="center">
                                {{ $dryer ? $dryer->created_at->format('d-m') : '' }}</td>
                            <td class="center">
                                {{ $dryer ? $dryer->nama_barang : '' }}</td>
                            <td class="center">
                                {{ $dryer ? $dryer->no_dryer : '' }}</td>
                            <td class="center">
                                {{ $timbanganItem ? $timbanganItem->kode : '-' }}</td>
                            <td class="right">
                                {{ $timbanganItem ? number_format($timbanganItem->total_netto, 0, ',', '.') : '-' }}
                            </td>
                            <td class="center">
                                @if ($i == 0)
                                    {{ $laporanlumbung->user->name }}
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="center">
                            Total Berat:</td>
                        <td class="right">
                            {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>

</html>
