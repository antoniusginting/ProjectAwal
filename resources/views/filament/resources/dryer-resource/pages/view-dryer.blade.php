<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full align-left">
                {{-- @php
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
                @endphp --}}
                @php
                    // Hitung total netto bersih dari semua sortirans
                    $grandTotalNetto = $dryer->sortirans->sum(function ($sortiran) {
                        // Hilangkan titik ribuan, lalu konversi
                        $str = str_replace('.', '', $sortiran->netto_bersih);
                        return is_numeric($str) ? (float) $str : 0;
                    });
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
                        <td class="whitespace-nowrap">:
                            {{ number_format($dryer->kapasitasdryer->kapasitas_total, '0', ',', '.') }}</td>

                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">: {{ $dryer->operator }}</td>
                        <td class="font-semibold whitespace-nowrap">Hasil Kadar</td>
                        <td class="whitespace-nowrap">: {{ $dryer->hasil_kadar }}%</td>
                        <td class="font-semibold whitespace-nowrap">Kapasitas Terpakai</td>
                        <td>: {{ number_format($grandTotalNetto,'0',',','.') }}</td>
                        {{-- <td class="whitespace-nowrap">: {{ number_format($totalBerat, '0', ',', '.') }}</td> --}}
                        {{-- {{ number_format($dryer->kapasitasdryer->kapasitas_total - $totalBerat, 0, ',', '.') }} --}}
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
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Timbangan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kadar</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedSortirans = $dryer->sortirans->groupBy('id_sortiran');
                    @endphp

                    @foreach ($groupedSortirans as $idSortiran => $sortiransGroup)
                        @php
                            $totalNettoBersih = 0;
                            $totalTotalKarung = 0;
                        @endphp

                        @foreach ($sortiransGroup as $index => $sortiran)
                            <tr>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700" width='50px'>
                                    {{ $sortiran->created_at->format('d/m') ?? '-' }}</td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                    {{ $sortiran->pembelian->nama_barang }}</td>
                                <td class="border p-2 border-gray-300 dark:border-gray-700 text-right" width='200px'>
                                    {{ $sortiran->total_karung ?? '-' }}
                                </td>
                                <td class="border text-right p-2 border-gray-300 dark:border-gray-700">
                                    {{ $sortiran->netto_bersih ?? '-' }}
                                </td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                    {{ $sortiran->pembelian->no_spb ?? '-' }}
                                </td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                    {{ $sortiran->kadar_air ?? '-' }}%
                                </td>
                                @php
                                    // Hapus pemisah ribuan (titik) dari nilai netto_bersih
                                    $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih);

                                    // Cek jika netto_bersih bisa dikonversi menjadi angka setelah penghapusan titik
                                    $nettoBersihValue = is_numeric($nettoBersihStripped)
                                        ? floatval($nettoBersihStripped)
                                        : 0;
                                    $totalNettoBersih += $nettoBersihValue;

                                    // Hapus pemisah ribuan (titik) dari nilai netto_bersih
                                    $totalKarungStripped = str_replace('.', '', $sortiran->total_karung);

                                    // Cek jika netto_bersih bisa dikonversi menjadi angka setelah penghapusan titik
                                    $totalKarungValue = is_numeric($totalKarungStripped)
                                        ? floatval($totalKarungStripped)
                                        : 0;
                                    $totalTotalKarung += $totalKarungValue;
                                @endphp
                            </tr>
                        @endforeach

                        <!-- Displaying Total Netto Bersih for this group -->
                        <tr>
                            <td colspan="2"
                                class="text-center font-semibold p-2 border-gray-300 dark:border-gray-700">
                                Total {{ $idSortiran }}
                            </td>
                            <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                {{ number_format($totalTotalKarung, 0, ',', '.') }}
                            </td>
                            <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                {{ number_format($totalNettoBersih, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- <tbody>


                    @foreach ($allSortirans as $sortiran)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                {{ $sortiran->created_at->format('d') ?? '-' }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
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
                {{-- </tr>
                    @endforeach
                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                        <td class="" colspan="2"></td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-600">{{ $totalGoni }}
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-600">
                            {{ number_format($totalBerat, '0', ',', '.') }}</td>
                        <td class="" colspan="2"></td>
                    </tr>
                </tbody> --}}


            </table>
        </div>
        <table class="w-full border border-gray-300 border-collapse text-center">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-4 w-1/2">TTD Operator</th>
                    <th class="border border-gray-300 p-4 2-1/2">TTD Penanggung Jawab</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 p-8 align-bottom h-32">
                        <!-- Tempat tanda tangan Operator -->
                        <div class="mt-16">
                            <span class="block border-b border-black w-40 mx-auto"></span>
                            {{-- <p class="mt-2 font-semibold">{{ $operatorName ?? 'Nama Operator' }}</p>
                            <p class="text-sm text-gray-600">{{ $operatorPosition ?? 'Jabatan Operator' }}</p> --}}
                        </div>
                    </td>
                    <td class="border border-gray-300 p-8 align-bottom h-32">
                        <!-- Tempat tanda tangan Penanggung Jawab -->
                        <div class="mt-16">
                            <span class="block border-b border-black w-40 mx-auto"></span>
                            {{-- <p class="mt-2 font-semibold">{{ $penanggungJawabName ?? 'Nama Penanggung Jawab' }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $penanggungJawabPosition ?? 'Jabatan Penanggung Jawab' }}</p> --}}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>


    </div>
</x-filament-panels::page>
