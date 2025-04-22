<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Header Surat -->
        <div class="text-left space-y-1">
            <h1 class="text-3xl font-bold">Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2 class="text-lg">Surat Timbangan penjualan</h2>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>
        


        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            
            <table class="w-full">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left align-top whitespace-nowrap">Tanggal</td>
                        <td class="whitespace-nowrap">: {{ $penjualan->created_at->format('d-m-Y') }}</td>
                        <td class="font-semibold whitespace-nowrap">Jam</td>
                        <td class="whitespace-nowrap">: {{ $penjualan->created_at->format('H:i') }}</td>
                        <td class="font-semibold whitespace-nowrap">Nama Lumbung</td>
                        <td class="whitespace-nowrap">: {{ $penjualan->nama_lumbung}}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">: {{ $penjualan->user->name }}</td>
                        <td class="font-semibold text-left align-top whitespace-nowrap">Container</td>
                        <td class="whitespace-nowrap">: {{ $penjualan->no_container }}</td>
                        <td class="font-semibold text-left align-top whitespace-nowrap">No Lumbung</td>
                        <td class="whitespace-nowrap">: {{ $penjualan->no_lumbung }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <div class="text-right text-sm mb-2">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No SPB</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Plat Polisi</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Supir</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Satuan Muatan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Barang</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm" colspan="2">Berat</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap" rowspan="3">
                            {{ $penjualan->no_spb }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap" rowspan="3">
                            {{ $penjualan->plat_polisi }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap" rowspan="3">
                            {{ $penjualan->nama_supir }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap" rowspan="3">
                            @if ($penjualan->brondolan == 'GONI')
                            @php
                                $adaGoni = true;
                            @endphp
                            {{ $penjualan->jumlah_karung }} - {{ $penjualan->brondolan }}
                        @else
                            {{ $penjualan->brondolan }}
                        @endif
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap" rowspan="3">
                            {{ $penjualan->nama_barang }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 whitespace-nowrap">Bruto</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                            {{ number_format($penjualan->bruto, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 whitespace-nowrap">Tara</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                            {{ number_format($penjualan->tara, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 whitespace-nowrap">Netto</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                            {{ number_format($penjualan->netto, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">TTD OPERATOR</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-gray-500 dark:text-gray-400">Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 dark:border-gray-700 w-56 mx-auto"></div>
            </div>
        </div>
        
    </div>
</x-filament-panels::page>
