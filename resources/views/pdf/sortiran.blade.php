<style>
    /* Gaya kontainer utama */
.container {
    padding: 16px;
    background-color: #ffffff;
    max-width: 896px;
    margin: 0 auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 
                0 1px 2px rgba(0, 0, 0, 0.06);
    border-radius: 8px;
}

/* Nilai data */
.value {
    color: #4B5563;
    margin-top: 0;
    margin-bottom: 0;
}

/* Styling untuk tautan */
a {
    color: black;
}

/* Pembatas antara konten dan tabel */
.table-container {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 8px;
    border-top: 1px solid #d1d5db;
}

/* Gaya tabel */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}
table, th, td {
    border: 1px solid #d1d5db;
}
th, td {
    padding: 6px 12px; /* Reduced padding */
}
th {
    background-color: #f3f4f6;
}
.text-center {
    text-align: center;
}
tr.total {
    background-color: #e5e7eb;
    font-weight: 600;
}

/* Responsif untuk layar kecil */
@media (max-width: 768px) {
    .column {
        width: 100%;
    }
    
    .column:first-child {
        margin-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 12px;
    }
}

/* Khusus untuk print */
@media print {
    .card-inner {
        display: flex;
        page-break-inside: avoid;
        padding: 12px 8px;
    }
    
    .column {
        width: 50%;
        page-break-inside: avoid;
    }
    
    .container {
        box-shadow: none;
        padding: 0;
    }
    
    .card {
        padding: 0;
    }
    
    .card-inner {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .item {
        margin-bottom: 4px;
    }
}
</style>

<div class="container">
    <div class="table-container">
        <table>
            <tbody>
                <tr>
                    <td>No SPB</td>
                    <td>: {{ $sortiran->pembelian->no_spb }}</td>
                    <td>Bruto</td>
                    <td>: {{ number_format($sortiran->bruto ?? 19000, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Supplier</td>
                    <td>: {{ $sortiran->pembelian->supplier->nama_supplier }}</td>
                    <td>Tara</td>
                    <td>: {{ number_format($sortiran->tara ?? 4000, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Plat Polisi</td>
                    <td>: {{ $sortiran->pembelian->plat_polisi }}</td>
                    <td>Netto</td>
                    <td>: {{ number_format($sortiran->netto ?? 15000, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kadar Air</td>
                    <td>: {{ $sortiran->kadar_air }}%</td>
                    <td>Timbangan</td>
                    <td>: ke - {{ $sortiran->pembelian->keterangan }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ $sortiran->created_at }}</td>
                    <td>Lumbung Basah</td>
                    <td>: {{ $sortiran->no_lumbung }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="table-container">
        <table>
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
                        $tonase = $jumlah_karung > 0 
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
                        <td>{{ $sortiran["kualitas_jagung_$i"] ?? '-' }}</td>
                        <td>{{ $sortiran["x1_x10_$i"] ?? '-' }}</td>
                        <td class="text-center">{{ $jumlah_karung }}</td>
                        <td class="text-center">{{ number_format($tonase, 3, ',', '.') }}</td>
                    </tr>
                @endfor
                <tr class="total">
                    <td colspan="3" class="text-center">Total</td>
                    <td class="text-center">{{ $total_karung }}</td>
                    <td class="text-center">{{ number_format($total_tonase, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>