<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full align-left">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left whitespace-nowrap" width='180px'>ID Lumbung</td>
                        <td class="whitespace-nowrap" width='200px'>: {{ $lumbungbasah->no_lb }}</td>
                        <td class="font-semibold whitespace-nowrap" width='250px'>Kapasitas Lumbung Basah</td>
                        <td class="whitespace-nowrap" width='180px'>:
                            {{ number_format($lumbungbasah->kapasitaslumbungbasah->kapasitas_total, 0, ',', '.') }}</td>
                        <td class="font-semibold text-left align-top whitespace-nowrap" width='200px'>Kapasitas Sisa
                        </td>
                        <td class="whitespace-nowrap">:
                            {{ number_format($lumbungbasah->kapasitaslumbungbasah->kapasitas_sisa, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">No Lumbung</td>
                        <td class="whitespace-nowrap">: {{ $lumbungbasah->no_lumbung_basah }}</td>
                        <td class="font-semibold whitespace-nowrap">Tujuan Dryer</td>
                        <td class="whitespace-nowrap">: {{ $lumbungbasah->tujuan }}</td>
                        <td class="font-semibold whitespace-nowrap">Kapasitas Terpakai</td>
                        <td class="whitespace-nowrap">:
                            {{ number_format($lumbungbasah->kapasitaslumbungbasah->kapasitas_total - $lumbungbasah->kapasitaslumbungbasah->kapasitas_sisa, 0, ',', '.') }}
                        </td>

                    </tr>
                </tbody>
            </table>
        </div>


        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <div class="text-right text-sm mb-2">Tanggal: {{ $lumbungbasah->created_at->format('d-m-Y H:i:s') }}
                </div>
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Id Sortiran</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Lumbung</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tonase</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedSortirans = $lumbungbasah->sortirans->groupBy('id_sortiran');
                    @endphp

                    @foreach ($groupedSortirans as $idSortiran => $sortiransGroup)
                        @php
                            $totalNettoBersih = 0;
                        @endphp

                        @foreach ($sortiransGroup as $index => $sortiran)
                            <tr>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700" width='50px'>
                                    {{ $index + 1 }}</td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                    {{ $sortiran->no_sortiran }} - {{ $sortiran->pembelian->nama_supir }} -
                                    {{ $sortiran->pembelian->plat_polisi }}</td>
                                <td class="border p-2 border-gray-300 dark:border-gray-700 text-center" width='200px'>
                                    {{ $sortiran->no_lumbung }}
                                </td>
                                <td class="border text-right p-2 border-gray-300 dark:border-gray-700">
                                    {{ $sortiran->netto_bersih ?? '-' }}
                                </td>
                                @php
                                    // Hapus pemisah ribuan (titik) dari nilai netto_bersih
                                    $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih);

                                    // Cek jika netto_bersih bisa dikonversi menjadi angka setelah penghapusan titik
                                    $nettoBersihValue = is_numeric($nettoBersihStripped)
                                        ? floatval($nettoBersihStripped)
                                        : 0;
                                    $totalNettoBersih += $nettoBersihValue;
                                @endphp
                            </tr>
                        @endforeach

                        <!-- Displaying Total Netto Bersih for this group -->
                        <tr>
                            <td colspan="3"
                                class="text-center font-semibold p-2 border-gray-300 dark:border-gray-700">
                                Total Tonase {{ $idSortiran }}
                            </td>
                            <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                {{ number_format($totalNettoBersih, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>


            </table>
        </div>

        {{-- <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">TTD OPERATOR</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-gray-500 dark:text-gray-400">Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 dark:border-gray-700 w-56 mx-auto"></div>
            </div>
        </div> --}}

    </div>
</x-filament-panels::page>
