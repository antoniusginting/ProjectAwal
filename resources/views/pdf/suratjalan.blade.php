<style>
    .container {
        padding: 24px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 24px;
        max-width: 800px;
        margin: 20px auto;
    }

    /* Header Surat */
    .header-surat {
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .header-surat h1 {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }

    .header-surat h2 {
        font-size: 16px;
        margin: 0;
    }

    /* Info Kota dan Tanggal */
    .info-kota-tanggal {
        display: flex;
        justify-content: space-between;
        font-size: 16px;
    }

    .info-kota-tanggal .kota-tanggal span {
        display: block;
    }

    /* Tujuan Pengiriman */
    .tujuan-pengiriman p {
        margin: 0 0 0px;
        line-height: 1.4;
    }

    .tujuan-pengiriman strong {
        font-weight: bold;
    }

    /* Divider untuk memisahkan judul dan tabel */
    .divider {
        border: none;
        border-top: 1px solid #ccc;
        margin: 16px 0;
    }

    /* Tabel Detail Pengiriman */
    .table-container {
        overflow-x: auto;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .detail-table th,
    .detail-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    .detail-table thead tr {
        background-color: #f3f3f3;
    }

    /* Tanda Tangan */
    .tanda-tangan {
        display: flex;
        justify-content: flex-end;
        margin-top: 40px;
    }

    .signature-wrapper {
        text-align: right;
    }

    .signature-wrapper p {
        font-size: 18px;
        font-weight: bold;
        margin: 0;
    }

    .signature-box {
        margin-top: 16px;
        height: 66px;
        width: 256px;
        /* Border dihilangkan sesuai permintaan */
        /* border: 1px solid #444; */
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    .signature-box span {
        color: #000;
    }

    .signature-line {
        margin-top: 16px;
        border-bottom: 2px solid #444;
        width: 224px;
        margin-left: auto;
    }
</style>

<div class="container">

    <!-- Header Surat -->
    <div class="header-surat">
        <h1>{{ $suratjalan->kontrak2->nama }}</h1>
        <h2>Surat Jalan Pengiriman</h2>
    </div>
    <div class="divider"></div>

    <!-- Info Kota dan Tanggal -->
    <div class="info-kota-tanggal">
        <div class="kota-tanggal">
            <span></span>
        </div>
        <div class="empty">
            <!-- Tempat untuk informasi tambahan bila diperlukan -->
        </div>
    </div>
    <div>
        <table style="width: 100%;">
            <tbody>
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
                <tr>
                    <td style="width: 20%; font-weight: bold;">No PO</td>
                    <td style="width: 80%;">: {{ $suratjalan->po }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    

    <!-- Pembatas antara judul dan tabel -->
    <div class="divider"></div>

    <!-- Tabel Detail Pengiriman -->
    <div class="table-container">
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
                    <td rowspan="3">
                        @if (!empty($suratjalan->tronton->penjualan1->plat_polisi))
                            {{ $suratjalan->tronton->penjualan1->plat_polisi }}
                        @else
                            {{ $suratjalan->tronton->penjualan1->no_container }}
                        @endif
                    </td>
                    <td rowspan="3">{{ $suratjalan->tronton->penjualan1->nama_supir }}</td>
                    <td rowspan="3">JAGUNG KERING SUPER</td>
                    <td rowspan="3">{{ $suratjalan->tronton->penjualan1->nama_barang }}</td>
                    <td>Bruto</td>
                    <td>{{ number_format($suratjalan->bruto_final, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tara</td>
                    <td>{{ number_format($suratjalan->tronton->tara_awal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Netto</td>
                    <td>{{ number_format($suratjalan->netto_final, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <div class="tanda-tangan">
        <div class="signature-wrapper">
            <p>Diterima Oleh</p>
            <div class="signature-box">
            </div>
            <div class="signature-line"></div>
        </div>
    </div>

</div>
