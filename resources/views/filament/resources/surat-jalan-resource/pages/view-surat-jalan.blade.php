<x-filament-panels::page>
    <div class="p-6 bg-white rounded-md shadow-md space-y-6">

        <!-- Header Surat -->
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-bold">{{ $suratjalan->kontrak2->nama }}</h1>
            <h2 class="text-lg">Surat Jalan Pengiriman</h2>
        </div>

        <!-- Info Kota dan Tanggal -->
        <div class="flex justify-between">
            <div>
                <!-- Kosongkan atau isi sesuai kebutuhan -->
                <span class="block"> {{ $suratjalan->kota }} , {{ $suratjalan->created_at->format('d-m-Y') }} </span>
            </div>
            <div>
                <!-- Kosongkan atau isi sesuai kebutuhan -->
                <span class="block"></span>
            </div>
        </div>

        <!-- Tujuan Pengiriman -->
        <div class="space-y-1">
            <p>Kepada Yth: <strong> {{ $suratjalan->kontrak->nama }} </strong></p>
            <p>Alamat : {{ $suratjalan->alamat->alamat }}</p>
            <p>No PO: {{ $suratjalan->po }}</p>
        </div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">Plat Polisi</th>
                        <th class="border p-2">Nama Supir</th>
                        <th class="border p-2">Brondolan</th>
                        <th class="border p-2">Nama Barang</th>
                        <th class="border p-2" colspan="2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border p-2 text-center" rowspan="3">BK</td>
                        <td class="border p-2 text-center" rowspan="3">Supri</td>
                        <td class="border p-2 text-center" rowspan="3">Curah</td>
                        <td class="border p-2 text-center" rowspan="3">Jagung Kering Super</td>
                        <td class="border p-2">Bruto</td>
                        <td class="border p-2">{{ number_format($suratjalan->tronton->bruto_final, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="border p-2">Tara</td>
                        <td class="border p-2">{{ number_format($suratjalan->tronton->penjualan1->tara, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2">Netto</td>
                        <td class="border p-2">{{ number_format($suratjalan->netto, 0, ',', '.') }}</td>
                    </tr>
                    <!-- Tambahkan baris lain jika diperlukan -->
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">Diterima Oleh</p>
                <div class="mt-4 h-24 w-64  mx-auto flex items-center justify-center">
                    <span class="text-white">Tanda Tangan</span>
                </div> <!-- Kotak besar untuk tanda tangan -->
                <div class="mt-4 border-b-2 border-gray-700 w-56 mx-auto"></div>
            </div>
        </div>



    </div>
</x-filament-panels::page>
