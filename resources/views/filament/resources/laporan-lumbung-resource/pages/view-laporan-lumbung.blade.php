<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full align-left">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left whitespace-nowrap" width='180px'>Tanggal</td>
                        <td class="whitespace-nowrap" width='200px'>:
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-y') : 'Tanggal kosong' }}
                        <td class="font-semibold whitespace-nowrap" width='250px'>No Laporan</td>
                        <td class="whitespace-nowrap" width='180px'>: {{ $laporanlumbung->kode }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Jam</td>
                        <td class="whitespace-nowrap">:
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('h:i') : 'Tanggal kosong' }}
                        </td>
                        <td class="font-semibold whitespace-nowrap">
                            {{ $laporanlumbung->status_silo ? 'Lumbung' : 'Lumbung' }}
                        </td>
                        <td class="whitespace-nowrap">:
                            {{ $laporanlumbung->lumbung ?? ($laporanlumbung->status_silo ?? '-') }}
                        </td>
                        </td>

                    </tr>
                </tbody>
            </table>
        </div>


        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>


        @php
            $lumbungTujuan = $laporanlumbung->lumbung ?? null;
        @endphp

        @foreach ($laporanlumbung->timbangantrontons as $timbanganTronton)
            @php
                $allPenjualan = collect();
                $relasiPenjualan = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];

                foreach ($relasiPenjualan as $relasi) {
                    if (isset($timbanganTronton->$relasi)) {
                        $dataRelasi = $timbanganTronton->$relasi;

                        if ($dataRelasi instanceof \Illuminate\Database\Eloquent\Collection) {
                            $allPenjualan = $allPenjualan->merge($dataRelasi);
                        } elseif ($dataRelasi !== null) {
                            $allPenjualan->push($dataRelasi);
                        }
                    }
                }

                $filteredPenjualan = $allPenjualan->where('nama_lumbung', $lumbungTujuan);
                $totalNetto = $filteredPenjualan->sum('netto');
            @endphp
        @endforeach
        @php
            $lumbungTujuan = $laporanlumbung->lumbung ?? null;
            $dryers = $laporanlumbung->dryers->values();
            $timbangan = $laporanlumbung->timbangantrontons->values();
            $max = max($dryers->count(), $timbangan->count());
            // Hitung total keseluruhan dari filtered netto
            $totalKeseluruhanFiltered = 0;
            $nilai_dryers_sum_total_netto = $dryers->sum('total_netto');

            // Hitung total netto dari relasi penjualans yang baru (di luar loop)
            $totalNettoPenjualansBaru = $laporanlumbung->penjualans->sum('netto') ?? 0;

            // //Hitung total langsir masuk
            // $nilai_langsir_masuk = $laporanlumbung->penjualans->sum
            // Total gabungan dideklarasikan di sini
            $totalGabungan = 0;
        @endphp

        <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Masuk</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Keluar</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">PJ</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $max; $i++)
                    @php
                        $dryer = $dryers->get($i);
                        $timbanganItem = $timbangan->get($i);

                        // Proses untuk mendapatkan filtered penjualan jika ada timbanganItem
                        $filteredPenjualan = collect();
                        $totalNetto = 0;

                        if ($timbanganItem) {
                            $allPenjualan = collect();
                            $relasiPenjualan = [
                                'penjualan1',
                                'penjualan2',
                                'penjualan3',
                                'penjualan4',
                                'penjualan5',
                                'penjualan6',
                            ];

                            foreach ($relasiPenjualan as $relasi) {
                                if (isset($timbanganItem->$relasi)) {
                                    $dataRelasi = $timbanganItem->$relasi;

                                    if ($dataRelasi instanceof \Illuminate\Database\Eloquent\Collection) {
                                        $allPenjualan = $allPenjualan->merge($dataRelasi);
                                    } elseif ($dataRelasi !== null) {
                                        $allPenjualan->push($dataRelasi);
                                    }
                                }
                            }

                            $filteredPenjualan = $allPenjualan->where('nama_lumbung', $lumbungTujuan);
                            $totalNetto = $filteredPenjualan->sum('netto');

                            // Tambahkan ke total keseluruhan
                            $totalKeseluruhanFiltered += $totalNetto;
                        }
                    @endphp
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $dryer ? $dryer->created_at->format('d-m') : '' }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $dryer ? $dryer->nama_barang : '' }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($dryer)
                                <a href="{{ route('filament.admin.resources.dryers.view-dryer', $dryer->id) }}"
                                    class="">
                                    {{ $dryer->no_dryer }}
                                </a>
                            @endif
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ $dryer && $dryer->total_netto ? number_format($dryer->total_netto, 0, ',', '.') : '' }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $timbanganItem ? $timbanganItem->kode : '' }}
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            @if ($timbanganItem)
                                @if ($filteredPenjualan->isEmpty())
                                    -
                                @else
                                    {{ number_format($totalNetto, 0, ',', '.') }}
                                @endif
                            @endif
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $timbanganItem ? $timbanganItem->user->name : '' }}
                        </td>
                    </tr>
                @endfor
            </tbody>

            <!-- Baris untuk menampilkan No SPB -->
            @if ($laporanlumbung->penjualans->isNotEmpty())
                @php
                    // Filter dan kelompokkan penjualan berdasarkan tipe_penjualan
                    $penjualanMasuk = $laporanlumbung->penjualans->filter(function ($penjualan) {
                        return !empty($penjualan->no_spb) && $penjualan->pivot->tipe_penjualan === 'masuk';
                    });

                    $penjualanKeluar = $laporanlumbung->penjualans->filter(function ($penjualan) {
                        return !empty($penjualan->no_spb) && $penjualan->pivot->tipe_penjualan === 'keluar';
                    });
                @endphp

                <!-- Tampilkan No SPB untuk tipe MASUK -->
                @if ($penjualanMasuk->isNotEmpty())
                    <tbody>
                        @foreach ($penjualanMasuk as $index => $penjualan)
                            <tr class="bg-green-50 dark:bg-green-900/20">
                                <td colspan="2"
                                    class="border p-2 text-center font-semibold border-gray-300 dark:border-gray-700 text-sm">
                                    @if ($index == 0)
                                        No SPB Masuk:
                                    @endif
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->no_spb }}
                                </td>
                                <td class="border text-right p-2 border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->netto ? number_format($penjualan->netto, 0, ',', '.') : '-' }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"></td>
                                <td class="border text-right p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->user->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif

                <!-- Tampilkan No SPB untuk tipe KELUAR -->
                @if ($penjualanKeluar->isNotEmpty())
                    <tbody>
                        @foreach ($penjualanKeluar as $index => $penjualan)
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td colspan="2"
                                    class="border p-2 text-center font-semibold border-gray-300 dark:border-gray-700 text-sm">
                                    @if ($index == 0)
                                        No SPB Keluar:
                                    @endif
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"></td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"></td>
                                <td width="250px"
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->no_spb }} - {{ $penjualan->silo }}
                                </td>
                                <td class="border text-right p-2 border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->netto ? number_format($penjualan->netto, 0, ',', '.') : '-' }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->user->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            @endif

            @php
                // Hitung total gabungan setelah loop selesai
                $totalGabungan = $totalKeseluruhanFiltered + $totalNettoPenjualansBaru;
            @endphp

            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                    @php
                        // Hitung selisih SETELAH loop selesai dan $totalKeseluruhanFilteredAccumulated sudah final
                        // $hasil_pengurangan_numeric_final = $nilai_dryers_sum_total_netto - $totalKeseluruhanFiltered;

                        // Cek apakah nilai_dryers_sum_total_netto tidak 0 sebelum pembagian
                        if ($nilai_dryers_sum_total_netto > 0) {
                            $hasil_pengurangan_numeric_final = ($totalGabungan / $nilai_dryers_sum_total_netto) * 100;
                        } else {
                            $hasil_pengurangan_numeric_final = 0; // atau bisa juga 'N/A'
                        }
                    @endphp
                    <td colspan="3" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                        Total Berat:
                    </td>
                    <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                        {{-- {{ number_format($nilai_dryers_sum_total_netto, 0, ',', '.') }} --}}
                        {{ $laporanlumbung->lumbung
                            ? number_format($nilai_dryers_sum_total_netto, 0, ',', '.')
                            : number_format($totalNettoPenjualansBaru, 0, ',', '.') }}
                    </td>
                    <td></td>
                    {{-- <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                        {{ $laporanlumbung->status_silo ?? '-' }}</td> --}}
                    <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                        @if ($laporanlumbung->lumbung)
                            {{ number_format($totalGabungan, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700 text-sm">
                        @if ($laporanlumbung->lumbung && $laporanlumbung->status)
                            {{ number_format($hasil_pengurangan_numeric_final, 2) }} %
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament-panels::page>
