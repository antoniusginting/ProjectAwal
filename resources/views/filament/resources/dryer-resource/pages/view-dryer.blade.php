<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full align-left">

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
                        <td class="whitespace-nowrap" width='200px'>: @if (!empty($dryer->pj))
                                {{ optional($dryer->created_at)->format('d-m-Y') }}
                            @endif
                        </td>
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
                        <td class="whitespace-nowrap">: @if (!empty($dryer->pj))
                                {{ optional($dryer->created_at)->format('h:i:s') }}
                            @endif
                        </td>
                        <td class="font-semibold whitespace-nowrap">Rencana Kadar</td>
                        <td class="whitespace-nowrap">: {{ $dryer->rencana_kadar }}@if ($dryer->rencana_kadar !== null)
                                %
                            @endif
                        </td>
                        <td class="font-semibold whitespace-nowrap">Kapasitas Dryer</td>
                        <td class="whitespace-nowrap">:
                            {{ number_format($dryer->kapasitasdryer->kapasitas_total, '0', ',', '.') }}</td>

                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">: {{ $dryer->operator }}</td>
                        <td class="font-semibold whitespace-nowrap">Hasil Kadar</td>
                        <td class="whitespace-nowrap">: {{ $dryer->hasil_kadar }}@if ($dryer->hasil_kadar !== null)
                                %
                            @endif
                        </td>
                        <td class="font-semibold whitespace-nowrap">Kapasitas Terpakai</td>
                        <td>: {{ $grandTotalNetto == 0 ? '' : number_format($grandTotalNetto, 0, ',', '.') }}</td>
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
            @if (!empty($dryer->pj))
                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    {{-- <div class="text-right text-sm mb-2">Tanggal: 
                </div> --}}
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Lumbung</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Goni</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Timbangan</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kadar</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">-</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">-</th>
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

                                // Hitung total netto bersih untuk grup ini terlebih dahulu
                                foreach ($sortiransGroup as $sortiran) {
                                    $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih);
                                    $nettoBersihValue = is_numeric($nettoBersihStripped)
                                        ? floatval($nettoBersihStripped)
                                        : 0;
                                    $totalNettoBersih += $nettoBersihValue;

                                    $totalKarungStripped = str_replace('.', '', $sortiran->total_karung);
                                    $totalKarungValue = is_numeric($totalKarungStripped)
                                        ? floatval($totalKarungStripped)
                                        : 0;
                                    $totalTotalKarung += $totalKarungValue;
                                }
                            @endphp

                            @foreach ($sortiransGroup as $index => $sortiran)
                                <tr>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                        width='50px'>
                                        {{ $sortiran->created_at->format('d/m') ?? '-' }}</td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                        width='150px'>
                                        {{ $sortiran->kapasitaslumbungbasah->no_kapasitas_lumbung }}</td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        {{ $sortiran->pembelian->nama_barang }}</td>
                                    <td class="border p-2 border-gray-300 dark:border-gray-700 text-right"
                                        width='100px'>
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
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        @php
                                            // Hitung persentase untuk kolom Tes
                                            $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih);
                                            $nettoBersihValue = is_numeric($nettoBersihStripped)
                                                ? floatval($nettoBersihStripped)
                                                : 0;

                                            $percentage =
                                                $totalNettoBersih > 0
                                                    ? round(($nettoBersihValue / $totalNettoBersih) * 100, 1)
                                                    : 0;

                                            // Hitung CA (kadar_air * percentage)
                                            $kadarAirValue = is_numeric($sortiran->kadar_air)
                                                ? round(floatval($sortiran->kadar_air), 2)
                                                : 0;
                                            $caValue = round($kadarAirValue * $percentage, 2);
                                        @endphp
                                        {{ number_format($percentage, 1, ',', '.') }}%
                                    </td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        {{ number_format($caValue, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Displaying Total Netto Bersih for this group -->
                            <tr>
                                <td colspan="3"
                                    class="text-center font-semibold p-2 border-gray-300 dark:border-gray-700">
                                    Total {{ $idSortiran }}
                                </td>
                                <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                    {{ number_format($totalTotalKarung, 0, ',', '.') }}
                                </td>
                                <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                    {{ number_format($totalNettoBersih, 0, ',', '.') }}
                                </td>
                                <td colspan="3" class="p-2 text-center border-gray-300 dark:border-gray-700">

                                </td>
                                <td class="p-2 text-center border-gray-300 dark:border-gray-700">
                                    @php
                                        // Hitung total CA untuk grup ini
                                        $totalCA = 0;
                                        $totalCB = 0;
                                        foreach ($sortiransGroup as $sortiran) {
                                            $nettoBersihStripped = str_replace('.', '', $sortiran->netto_bersih);
                                            $nettoBersihValue = is_numeric($nettoBersihStripped)
                                                ? floatval($nettoBersihStripped)
                                                : 0;
                                            $percentage =
                                                $totalNettoBersih > 0
                                                    ? round(($nettoBersihValue / $totalNettoBersih) * 100, 1)
                                                    : 0;
                                            $kadarAirValue = is_numeric($sortiran->kadar_air)
                                                ? floatval($sortiran->kadar_air)
                                                : 0;
                                            $caValue = $kadarAirValue * $percentage;
                                            $cbValue = ($kadarAirValue * $percentage) / 100;
                                            $totalCA += $caValue;
                                            $totalCB += $cbValue;
                                        }
                                    @endphp
                                    {{ number_format($totalCA, 2, ',', '.') }} ==
                                    {{ number_format($totalCB, 2, ',', '.') }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                {{-- Contoh Tabel Alternatif Sederhana --}}
                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Lumbung</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Goni</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Timbangan</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kadar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                            <tr>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700"></td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700"></td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700"></td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700"></td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700"></td>
                                <td class="border text-center p-2 border-gray-300 dark:border-gray-700"></td>
                                <td class="border text-right p-2 border-gray-300 dark:border-gray-700">%</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @endif
            <br>
            <br>
            <!-- Header Simple -->
            <div class="bg-white border-b-2 border-blue-500 p-4 mb-4">
                <h2 class="text-2xl font-bold text-gray-800 text-center">TABEL RANGKUMAN DATA SORTIRAN</h2>
                {{-- <p class="text-gray-600 text-center mt-1">Rekapitulasi Data Sortiran Jagung</p> --}}
            </div>
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Timbangan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tungkul</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis Jagung</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">X1-X10</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jumlah Karung</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tonase</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedSortirans = $dryer->sortirans->groupBy('id_sortiran');
                    @endphp

                    @foreach ($groupedSortirans as $idSortiran => $sortiransGroup)
                        @foreach ($sortiransGroup as $index => $sortiran)
                            @php
                                // Kumpulkan semua kualitas yang tidak null
                                $kualitasData = [];
                                for ($i = 1; $i <= 6; $i++) {
                                    $kualitas = $sortiran->{"kualitas_jagung_$i"};
                                    $x1_10 = $sortiran->{"x1_x10_$i"};
                                    $jumlah_karung = $sortiran->{"jumlah_karung_$i"};
                                    $tonase = $sortiran->{"tonase_$i"};

                                    if ($kualitas !== null && $kualitas !== '') {
                                        $kualitasData[] = [
                                            'kualitas' => $kualitas,
                                            'x1_10' => $x1_10,
                                            'jumlah_karung' => $jumlah_karung,
                                            'tonase' => $tonase,
                                            'index' => $i,
                                        ];
                                    }
                                }
                            @endphp

                            @if (count($kualitasData) > 0)
                                @foreach ($kualitasData as $kIndex => $data)
                                    <tr>
                                        @if ($kIndex == 0)
                                            <!-- Tampilkan tanggal dan no timbangan hanya di baris pertama -->
                                            <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                                width='50px' rowspan="{{ count($kualitasData) }}">
                                                {{ $sortiran->created_at->format('d/m') ?? '-' }}
                                            </td>
                                            <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                                width='130px' rowspan="{{ count($kualitasData) }}">
                                                {{ $sortiran->pembelian->no_spb ?? '-' }}
                                            </td>
                                            <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                                width='70px' rowspan="{{ count($kualitasData) }}">
                                                {{ number_format($sortiran->pembelian->netto ?? '-', 0, ',', '.') }}
                                            </td>
                                            <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                                width='50px' rowspan="{{ count($kualitasData) }}">
                                                {{ $sortiran->berat_tungkul ?? '-' }}
                                            </td>
                                        @endif

                                        <!-- Jenis Jagung per baris -->
                                        <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                            width='180px'>
                                            {{ $data['kualitas'] }}
                                        </td>

                                        <!-- X1-X10 per baris -->
                                        <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                            width='100px'>
                                            {{ $data['x1_10'] ?? '-' }}
                                        </td>

                                        <!-- Jumlah Karung per baris -->
                                        <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                            width='180px'>
                                            {{ $data['jumlah_karung'] ?? '-' }}
                                        </td>
                                        <!-- Jumlah Tonase per baris -->
                                        <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                            width='180px'>
                                            {{ $data['tonase'] ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <!-- Jika tidak ada kualitas, tampilkan baris kosong -->
                                <tr>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                        width='50px'>
                                        {{ $sortiran->created_at->format('d/m') ?? '-' }}
                                    </td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        {{ $sortiran->pembelian->no_spb ?? '-' }}
                                    </td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700"
                                        width='150px'>
                                        <span class="text-gray-400 italic">-</span>
                                    </td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        <span class="text-gray-400 italic">-</span>
                                    </td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        <span class="text-gray-400 italic">-</span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
</x-filament-panels::page>
