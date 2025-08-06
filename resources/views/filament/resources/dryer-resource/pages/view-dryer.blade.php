<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        <!-- Info Pengiriman -->
        {{-- @php
            $hasilPengurangan = $dryer->getHasilPenguranganNumericFinal();
        @endphp --}}
        {{-- {{ number_format($hasilPengurangan, 2) }}% --}}
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
                        <td class="font-semibold whitespace-nowrap">Kapasitas Terpakai</td>
                        <td>: {{ $grandTotalNetto == 0 ? '' : number_format($grandTotalNetto, 0, ',', '.') }}</td>

                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">: {{ $dryer->operator }}</td>
                        <td class="font-semibold whitespace-nowrap">Hasil Kadar</td>
                        <td class="whitespace-nowrap">: {{ $dryer->hasil_kadar }}@if ($dryer->hasil_kadar !== null)
                                %
                            @endif
                        </td>
                        <td class="font-semibold whitespace-nowrap">No IO</td>
                        <td class="whitespace-nowrap">: {{ $dryer->laporanLumbung->kode ?? '-' }}</td>
                        {{-- <td class="whitespace-nowrap">: {{ number_format($totalBerat, '0', ',', '.') }}</td> --}}
                        {{-- {{ number_format($dryer->kapasitasdryer->kapasitas_total - $totalBerat, 0, ',', '.') }} --}}
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Jenis Barang</td>
                        <td class="whitespace-nowrap">: {{ $dryer->nama_barang ?? '-' }}</td>
                        <td class="font-semibold whitespace-nowrap">No Dryer</td>
                        <td class="whitespace-nowrap">: {{ $dryer->no_dryer ?? '-' }}</td>
                        <td class="font-semibold whitespace-nowrap">Lumbung Tujuan</td>
                        <td class="whitespace-nowrap">: {{ $dryer->laporanLumbung->lumbung }}</td>
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
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Lumbung</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Supplier</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Goni</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Timbangan</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kadar</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">N * K</th>
                            {{-- <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">-</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">-</th> --}}
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
                                $totalPercentage = 0;
                                $persen = 0;

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
                                        {{ $sortiran->pembelian->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        {{ $sortiran->pembelian->nama_barang ?? 'JAGUNG KERING SUPER' }}</td>
                                    <td class="border p-2 border-gray-300 dark:border-gray-700 text-right"
                                        width='100px'>
                                        {{ $sortiran->total_karung ?? '-' }}
                                    </td>
                                    <td class="border text-right p-2 border-gray-300 dark:border-gray-700">
                                        {{ $sortiran->netto_bersih ?? '-' }}
                                    </td>
                                    <td class="border text-center p-2 border-gray-300 dark:border-gray-700">
                                        @php
                                            // Logic untuk menentukan no_spb
                                            $noSpb = '-';
                                            $isFromPembelian = false; // Flag untuk menentukan apakah SPB dari pembelian

                                            // Cek apakah pembelian no_spb ada dan tidak null
                                            if ($sortiran->pembelian && !empty($sortiran->pembelian->no_spb)) {
                                                $noSpb = $sortiran->pembelian->no_spb;
                                                $isFromPembelian = true; // Set flag true karena dari pembelian
                                            } else {
                                                // Jika pembelian no_spb null, ambil dari penjualan
                                                if ($sortiran->penjualans && $sortiran->penjualans->count() > 0) {
                                                    // Ambil semua no_spb dari penjualan dan gabungkan
                                                    $penjualanSpbs = $sortiran->penjualans
                                                        ->pluck('no_spb')
                                                        ->filter()
                                                        ->unique();
                                                    if ($penjualanSpbs->count() > 0) {
                                                        $noSpb = $penjualanSpbs->implode(', ');
                                                        $isFromPembelian = false; // Set flag false karena dari penjualan
                                                    }
                                                }
                                            }
                                        @endphp

                                        {{-- Tampilkan hyperlink hanya jika dari pembelian --}}
                                        @if ($isFromPembelian && $noSpb !== '-')
                                            <a href="{{ route('filament.admin.resources.sortirans.view-sortiran', $sortiran->id) }} "
                                                target="blank" class="underline">
                                                {{ $noSpb }}
                                            </a>
                                        @else
                                            {{ $noSpb }}
                                        @endif
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
                                                    ? round($nettoBersihValue * $sortiran->kadar_air)
                                                    : 0;
                                            $totalPercentage += $percentage;

                                            $persen = $totalPercentage / $totalNettoBersih;

                                        @endphp
                                        {{ number_format($percentage, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Displaying Total Netto Bersih for this group -->
                            <tr>
                                <td colspan="3"
                                    class="text-center font-semibold p-2 border-gray-300 dark:border-gray-700">
                                    Total {{ $idSortiran }}
                                </td>
                                <td></td>
                                <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                    {{ number_format($totalTotalKarung, 0, ',', '.') }}
                                </td>
                                <td class="p-2 text-right border-gray-300 dark:border-gray-700">
                                    {{ number_format($totalNettoBersih, 0, ',', '.') }}
                                </td>
                                <td colspan="2" class="p-2 text-center border-gray-300 dark:border-gray-700">

                                </td>
                                <td colspan="1" class="p-2 text-center border-gray-300 dark:border-gray-700">
                                    {{ number_format($persen, 2, ',', '.') }}%
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

            <div class="mb-4">
                @if ($dryer->laporanLumbung && $dryer->laporanLumbung->status == true)
                    <h1 class="text-2xl font-bold text-green-600 dark:text-green-400">
                        <span class="inline-flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Persentase Lumbung Kering
                            {{ number_format($dryer->laporanLumbung->persentase_keluar, 2) }}%
                        </span>
                    </h1>
                @else
                    <h1 class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                        <span class="inline-flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Belum Tutup Lumbung
                        </span>
                    </h1>
                @endif
            </div>


            <!-- Tabel Rangkuman berdasarkan Kualitas dan X1-X10 -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-4">Rangkuman Sortiran</h3>

                @php
                    $rangkumanData = [];
                    $groupedSortirans = $dryer->sortirans->groupBy('id_sortiran');

                    foreach ($groupedSortirans as $idSortiran => $sortiransGroup) {
                        foreach ($sortiransGroup as $sortiran) {
                            for ($i = 1; $i <= 6; $i++) {
                                $kualitas = $sortiran->{"kualitas_jagung_$i"};
                                $x1_10 = $sortiran->{"x1_x10_$i"};
                                $jumlah_karung = $sortiran->{"jumlah_karung_$i"};
                                $tonaseRaw = $sortiran->{"tonase_$i"};

                                // Normalisasi nilai tonase dari varchar (contoh: "1.250,75")
                                $cleanedTonase = str_replace(['.', ','], ['', '.'], $tonaseRaw);
                                $tonaseFloat = is_numeric($cleanedTonase) ? (float) $cleanedTonase : 0;

                                if ($kualitas !== null && $kualitas !== '') {
                                    $key = $kualitas . '|' . ($x1_10 ?? 'null');

                                    if (!isset($rangkumanData[$key])) {
                                        $rangkumanData[$key] = [
                                            'kualitas' => $kualitas,
                                            'x1_10' => $x1_10,
                                            'total_karung' => 0,
                                            'total_tonase' => 0,
                                            'count' => 0,
                                        ];
                                    }

                                    $rangkumanData[$key]['total_karung'] += (int) $jumlah_karung;
                                    $rangkumanData[$key]['total_tonase'] += $tonaseFloat;
                                    $rangkumanData[$key]['count']++;
                                }
                            }
                        }
                    }

                    // Kelompokkan berdasarkan kualitas
                    $groupedByKualitas = [];
                    foreach ($rangkumanData as $data) {
                        $kualitas = $data['kualitas'];
                        if (!isset($groupedByKualitas[$kualitas])) {
                            $groupedByKualitas[$kualitas] = [];
                        }
                        $groupedByKualitas[$kualitas][] = $data;
                    }

                    ksort($groupedByKualitas);
                @endphp

                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-300 dark:bg-gray-800">
                            <th class="border p-2 text-sm">Jenis Jagung</th>
                            <th class="border p-2 text-sm">X1-X10</th>
                            <th class="border p-2 text-sm">Frekuensi</th>
                            <th class="border p-2 text-sm">Total Karung</th>
                            <th class="border p-2 text-sm">Total Tonase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupedByKualitas as $kualitas => $dataGroup)
                            @php
                                $totalKarungKualitas = 0;
                                $totalTonaseKualitas = 0;
                                $totalFrekuensiKualitas = 0;
                            @endphp

                            @foreach ($dataGroup as $index => $data)
                                @php
                                    $totalKarungKualitas += $data['total_karung'];
                                    $totalTonaseKualitas += $data['total_tonase'];
                                    $totalFrekuensiKualitas += $data['count'];
                                @endphp

                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    @if ($index == 0)
                                        <td class="border text-center p-2 font-medium"
                                            rowspan="{{ count($dataGroup) + 1 }}">
                                            {{ $kualitas }}
                                        </td>
                                    @endif

                                    <td class="border text-center p-2">{{ $data['x1_10'] ?? '-' }}</td>
                                    <td class="border text-center p-2">{{ $data['count'] }}</td>
                                    <td class="border text-center p-2">
                                        {{ number_format($data['total_karung'], 0, ',', '.') }}
                                    </td>
                                    <td class="border text-center p-2">
                                        {{ number_format($data['total_tonase'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="bg-gray-100 dark:bg-blue-900/20 font-semibold">
                                <td class="border text-center p-2 italic">Subtotal</td>
                                <td class="border text-center p-2">{{ $totalFrekuensiKualitas }}</td>
                                <td class="border text-center p-2">
                                    {{ number_format($totalKarungKualitas, 0, ',', '.') }}
                                </td>
                                <td class="border text-center p-2">
                                    {{ number_format($totalTonaseKualitas, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        @php
                            $grandTotalKarung = array_sum(array_column($rangkumanData, 'total_karung'));
                            $grandTotalTonase = array_sum(array_column($rangkumanData, 'total_tonase'));
                            $grandTotalFrekuensi = array_sum(array_column($rangkumanData, 'count'));
                        @endphp

                        <tr class="bg-gray-200 dark:bg-green-900/30 font-bold">
                            <td class="border text-center p-2" colspan="2">GRAND TOTAL</td>
                            <td class="border text-center p-2">{{ $grandTotalFrekuensi }}</td>
                            <td class="border text-center p-2">
                                {{ number_format($grandTotalKarung, 0, ',', '.') }}
                            </td>
                            <td class="border text-center p-2">
                                {{ number_format($grandTotalTonase, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


        </div>
</x-filament-panels::page>
