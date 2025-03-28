<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        
        <!-- Header Surat -->
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-bold">{{ $suratjalan->kontrak2->nama }}</h1>
            <h2 class="text-lg">Surat Jalan Pengiriman</h2>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Info Kota dan Tanggal -->
        <div class="flex justify-between">
            <div>
                <span class="block">{{ $suratjalan->kota }}, {{ $suratjalan->created_at->format('d-m-Y') }}</span>
            </div>
            <div>
                <span class="block"></span>
            </div>
        </div>

        <!-- Info Pengiriman -->
        <div>
            <table class="w-full">
                <tbody>
                    <tr>
                        <td class="font-semibold text-left align-top">Kepada Yth.</td>
                        <td>: {{ $suratjalan->kontrak->nama }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Alamat</td>
                        <td>: {{ $suratjalan->alamat->alamat }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">No PO</td>
                        <td>: {{ $suratjalan->po }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700">
                            @if(!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                Plat Polisi
                            @else
                                No Container
                            @endif
                        </th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Nama Supir</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Brondolan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Nama Barang</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700" colspan="2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            @if(!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                {{ $suratjalan->tronton->penjualan1->plat_polisi }}
                            @else
                                {{ $suratjalan->tronton->penjualan1->no_container }}
                            @endif
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->nama_supir }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->brondolan }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->nama_barang }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">Bruto</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">
                            {{ number_format($suratjalan->tronton->bruto_final, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">Tara</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">
                            {{ number_format($suratjalan->tronton->tara_awal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">Netto</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">
                            {{ number_format($suratjalan->tronton->netto_final, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">Diterima Oleh</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-gray-500 dark:text-gray-400">Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 dark:border-gray-700 w-56 mx-auto"></div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
