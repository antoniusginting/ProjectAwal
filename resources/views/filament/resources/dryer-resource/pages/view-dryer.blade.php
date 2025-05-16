<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full align-left">
                @php
                    $allSortirans = collect();
                    $totalBerat = 0;
                    $totalGoni = 0;

                    // Collect sortirans from all lumbungs
                    for ($i = 1; $i <= 4; $i++) {
                        $lumbungProperty = "lumbung{$i}";
                        if (
                            isset($dryer->$lumbungProperty) &&
                            $dryer->$lumbungProperty &&
                            $dryer->$lumbungProperty->sortirans
                        ) {
                            foreach ($dryer->$lumbungProperty->sortirans as $sortiran) {
                                $sortiran->lumbung_name = "Lumbung {$i}";
                                $allSortirans->push($sortiran);

                                // Sanitasi nilai berat: hanya ambil angka dan titik desimal
                                $beratValue = $sortiran->netto_bersih;
                                if (is_string($beratValue)) {
                                    // Hapus ribuan format seperti 1.000 -> 1000
                                    $beratValue = str_replace('.', '', $beratValue);
                                    // Ganti koma desimal dengan titik jika ada
                                    $beratValue = str_replace(',', '.', $beratValue);
                                    // Hapus semua karakter non-numerik kecuali titik desimal
                                    $beratValue = preg_replace('/[^0-9.]/', '', $beratValue);
                                }
                                $beratValue = empty($beratValue) ? 0 : (float) $beratValue;

                                // Untuk goni (integer), cukup pastikan nilainya tidak null
                                $goniValue = $sortiran->total_karung ?? 0;

                                // Add to totals
                                $totalBerat += $beratValue;
                                $totalGoni += $goniValue;
                            }
                        }
                    }
                @endphp
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left whitespace-nowrap" width='180px'>Tanggal</td>
                        <td class="whitespace-nowrap" width='200px'>: {{ $dryer->created_at->format('d-m-Y') }}</td>
                        <td class="font-semibold whitespace-nowrap" width='250px'>Penanggung Jawab</td>
                        <td class="whitespace-nowrap" width='180px'>: {{ $dryer->pj }}
                        </td>
                        <td class="font-semibold text-left align-top whitespace-nowrap" width='200px'>Dryer/Panggangan
                        </td>
                        <td class="whitespace-nowrap">: {{ $dryer->kapasitasdryer->nama_kapasitas_dryer }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Jam</td>
                        <td class="whitespace-nowrap">: {{ $dryer->created_at->format('H-i-s') }}</td>
                        <td class="font-semibold whitespace-nowrap">Rencana Kadar</td>
                        <td class="whitespace-nowrap">: {{ $dryer->rencana_kadar }}% </td>
                        <td class="font-semibold whitespace-nowrap">Kapasitas Dryer</td>
                        <td class="whitespace-nowrap">: {{ number_format($dryer->kapasitasdryer->kapasitas_total,'0',',','.') }}</td>

                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">: {{ $dryer->operator }}</td>
                        <td class="font-semibold whitespace-nowrap">Hasil Kadar</td>
                        <td class="whitespace-nowrap">: {{ $dryer->hasil_kadar }}%</td>
                        <td class="font-semibold whitespace-nowrap">Kapasitas Terpakai</td>
                        <td class="whitespace-nowrap">: {{number_format( $totalBerat,'0',',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Jenis Barang</td>
                        <td class="whitespace-nowrap">: {{ $dryer->nama_barang }}</td>
                        <td class="font-semibold whitespace-nowrap">No Dryer</td>
                        <td class="whitespace-nowrap">: {{ $dryer->no_dryer }}</td>
                        <td class="font-semibold whitespace-nowrap">Lumbung Tujuan</td>
                        <td class="whitespace-nowrap">: {{ $dryer->lumbung_tujuan }} </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                {{-- <div class="text-right text-sm mb-2">Tanggal: 
                </div> --}}
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Goni</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No SPB</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kadar</th>
                    </tr>
                </thead>
                <tbody>


                    @foreach ($allSortirans as $sortiran)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                {{ $sortiran->created_at->format('d') ?? '-' }}
                            </td>
                            <td class="border p-2 border-gray-300 dark:border-gray-700">
                                {{ $sortiran->pembelian->nama_barang ?? '-' }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                {{ $sortiran->total_karung ?? '-' }}
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700">
                                {{ $sortiran->netto_bersih ?? '-' }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                {{ $sortiran->pembelian->no_spb ?? '-' }}</td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                {{ $sortiran->kadar_air ?? '-' }}%
                            </td>
                            {{-- <td class="border p-2 border-gray-300 dark:border-gray-700">{{ $sortiran->lumbung_name }}
                            </td> --}}
                        </tr>
                        <!-- Total Row -->
                    @endforeach
                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                        <td class="" colspan="2"></td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-600">{{ $totalGoni }}
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-600">
                            {{ number_format($totalBerat, '0', ',', '.') }}</td>
                        <td class="" colspan="2"></td>
                    </tr>
                </tbody>


            </table>
        </div>



    </div>
</x-filament-panels::page>
