<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Header Surat -->
        <div class="text-center space-y-1">
            <h1 class="text-3xl font-bold">Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2 class="text-lg">Laporan Penjualan</h2>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Info Pengiriman -->
        <div>
            <table class="w-full">
                <tbody>
                    <tr>
                        <td class="font-semibold text-left align-top">Tanggal</td>
                        <td>: {{ $timbangantronton->created_at->format('d-m-Y') }}</td>
                        <td class="font-semibold">Jam</td>
                        <td>: {{ $timbangantronton->created_at->format('H:i') }}</td>
                        <td class="font-semibold text-left align-top">No Penjualan</td>
                        <td>: {{ $timbangantronton->kode }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Operator</td>
                        <td>: {{ $timbangantronton->penjualan1->user->name }}</td>
                        <td class="font-semibold text-left align-top">Plat Polisi</td>
                        <td>: {{ $timbangantronton->penjualan1->plat_polisi }}</td>

                    </tr>
                    <tr>
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
                        <th class="border p-2 border-gray-300 dark:border-gray-700">No</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">No_SPB</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Jenis</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Satuan Muatan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Lumbung</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">No Lumbung/IO</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Berat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalNetto = 0;
                    @endphp

                    @for ($i = 1; $i <= 6; $i++)
                        @php
                            $penjualan = $timbangantronton->{'penjualan' . $i};
                        @endphp

                        @if ($penjualan)
                            <tr>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                    {{ $i }}
                                </td>
                                <td class="border p-2  border-gray-300 dark:border-gray-700">
                                    {{ $penjualan->no_spb }}
                                </td>
                                <td class="border p-2  border-gray-300 dark:border-gray-700">
                                    {{ $penjualan->nama_barang }}
                                </td>
                                <td class="border p-2  border-gray-300 dark:border-gray-700">
                                    {{ $penjualan->brondolan }}
                                </td>
                                <td class="border p-2 border-gray-300 dark:border-gray-700">
                                    {{ $penjualan->nama_lumbung }}
                                </td>
                                <td class="border p-2 border-gray-300 dark:border-gray-700">
                                    {{ $penjualan->no_lumbung }}
                                </td>
                                <td class="border p-2 border-gray-300 dark:border-gray-700 text-right">
                                    {{ number_format($penjualan->netto, 0, ',', '.') }}
                                </td>
                            </tr>

                            @php
                                $totalNetto += $penjualan->netto;
                            @endphp
                        @endif
                    @endfor

                    <!-- TOTAL -->
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="6" class="border p-2 text-center border-gray-300 dark:border-gray-700">Total
                            Netto</td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700">
                            {{ number_format($totalNetto, 0, ',', '.') }}
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
