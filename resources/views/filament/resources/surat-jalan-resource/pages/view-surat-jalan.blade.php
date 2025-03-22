<x-filament-panels::page>
    <div class="p-6 bg-white rounded-md shadow-md space-y-6">

        <!-- Header Surat -->
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-bold">{{ $suratjalan->kontrak2->nama }}</h1>
            <h2 class="text-lg">Surat Jalan Pengiriman</h2>
        </div>
        <!-- Divider: garis pembatas tipis -->
        <div class="border-b border-gray-300"></div>
        <!-- Info Kota dan Tanggal -->
        <div class="flex justify-between">
            <div>
                <span class="block">{{ $suratjalan->kota }} , {{ $suratjalan->created_at->format('d-m-Y') }}</span>
            </div>
            <div>
                <!-- Kosongkan atau isi sesuai kebutuhan -->
                <span class="block"></span>
            </div>
        </div>

        <!-- Info Pengiriman -->
        <div>
            <table>
                <tbody>
                    <tr>
                        <td>Kepada Yth</td>
                        <td>: {{ $suratjalan->kontrak->nama }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $suratjalan->alamat->alamat }}</td>
                    </tr>
                    <tr>
                        <td>No PO</td>
                        <td>: {{ $suratjalan->po }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider: garis pembatas tipis -->
        <div class="border-b border-gray-300"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">
                            @if(!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                Plat Polisi
                            @else
                                No Container
                            @endif
                        </th>
                        <th class="border p-2">Nama Supir</th>
                        <th class="border p-2">Brondolan</th>
                        <th class="border p-2">Nama Barang</th>
                        <th class="border p-2" colspan="2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border p-2 text-center" rowspan="3">
                            @if(!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                {{ $suratjalan->tronton->penjualan1->plat_polisi }}
                            @else
                                {{ $suratjalan->tronton->penjualan1->no_container }}
                            @endif
                        </td>
                        <td class="border p-2 text-center" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->nama_supir }}
                        </td>
                        <td class="border p-2 text-center" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->brondolan }}
                        </td>
                        <td class="border p-2 text-center" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->nama_barang }}
                        </td>
                        <td class="border p-2">Bruto</td>
                        <td class="border p-2">
                            {{ number_format($suratjalan->tronton->bruto_final, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2">Tara</td>
                        <td class="border p-2">
                            {{ number_format($suratjalan->tronton->penjualan1->tara, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2">Netto</td>
                        <td class="border p-2">
                            {{ number_format($suratjalan->netto, 0, ',', '.') }}
                        </td>
                    </tr>
                    <!-- Tambahkan baris lain jika diperlukan -->
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">Diterima Oleh</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center">
                    <!-- Kotak tanda tangan tanpa border -->
                    <span>Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 w-56 mx-auto"></div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
