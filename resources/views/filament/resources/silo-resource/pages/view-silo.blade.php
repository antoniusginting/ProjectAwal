<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        @php
            // Filter dan hitung total berat dari timbangan tronton
            $timbanganFiltered = $silo->timbanganTrontons
                ->map(function ($timbangan) use ($silo) {
                    $totalNetto = 0;

                    // Cek penjualan 1-6
                    for ($i = 1; $i <= 6; $i++) {
                        $penjualan = $timbangan->{"penjualan{$i}"} ?? null;
                        if ($penjualan) {
                            $namaLumbung =
                                $penjualan->nama ?? ($penjualan->nama_lumbung ?? ($penjualan->lumbung ?? ''));
                            if ($namaLumbung == $silo->nama) {
                                $totalNetto += $penjualan->netto ?? 0;
                            }
                        }
                    }

                    // Return object dengan data yang sudah difilter
                    return (object) [
                        'created_at' => $timbangan->created_at,
                        'kode' => $timbangan->kode,
                        'total_netto_filtered' => $totalNetto,
                        'original' => $timbangan,
                    ];
                })
                ->filter(function ($item) {
                    return $item->total_netto_filtered > 0; // Hanya ambil yang ada datanya
                });

            // Hitung total
            $totalBeratTrontonFiltered = $timbanganFiltered->sum('total_netto_filtered');

            // Hitung total berat dari laporan lumbung dengan prioritas field
            $totalBerat1 = 0;
            foreach ($silo->laporanlumbungs as $laporan) {
                // Jika berat_langsir ada nilai dan tidak 0, gunakan berat_langsir
                if (!empty($laporan->berat_langsir) && $laporan->berat_langsir > 0) {
                    $totalBerat1 += $laporan->berat_langsir;
                }
                // Jika berat_langsir null atau 0, gunakan hasil
                else {
                    $totalBerat1 += $laporan->hasil ?? 0;
                }
            }

            // Hitung summary
            $totalStokDanBerat = $silo->stok + $totalBerat1;
            $stokSisa = $totalStokDanBerat - $totalBeratTrontonFiltered;
            $persenan = $totalStokDanBerat != 0 ? ($totalBeratTrontonFiltered / $totalStokDanBerat) * 100 : 0;

        @endphp
        {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4"> --}}
        {{-- Summary Dashboard - Layout 1 Baris --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg mb-6 shadow-md border">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Ringkasan Stok {{ $silo->nama }}
            </h3>

            <div class="flex flex-row gap-4">
                <!-- Stok Awal -->
                <div class="flex-1 text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Stok Awal</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($silo->stok, 0, ',', '.') }}
                    </p>
                </div>

                <!-- Total Masuk -->
                <div class="flex-1 text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Masuk</p>
                    <p class="text-xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($totalBerat1, 0, ',', '.') }}
                    </p>
                </div>
                <!-- Total Stok -->
                <div class="flex-1 text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Stok</p>
                    <p class="text-xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($totalStokDanBerat, 0, ',', '.') }}
                    </p>
                </div>

                <!-- Total Keluar -->
                <div class="flex-1 text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Keluar</p>
                    <p class="text-xl font-bold text-orange-600 dark:text-orange-400">
                        {{ number_format($totalBeratTrontonFiltered, 0, ',', '.') }}
                    </p>
                </div>

                <!-- Stok Sisa -->
                <div
                    class="flex-1 text-center p-3 {{ $stokSisa >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Stok Sisa</p>
                    <p
                        class="text-xl font-bold {{ $stokSisa >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ number_format($stokSisa, 0, ',', '.') }}
                    </p>
                </div>
                <!-- Persenan -->
                <div
                    class="flex-1 text-center p-3 {{ $persenan >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Persenan</p>
                    <p
                        class="text-xl font-bold {{ $persenan >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ number_format($persenan, 2) }} %
                    </p>
                </div>
            </div>
        </div>
        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>
        {{-- Tabel 1: Laporan Lumbung --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Laporan Lumbung</h3>
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No IO</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Dryer</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($silo->laporanlumbungs as $laporan)
                        @php
                            // // Hitung berat untuk kolom pertama
                            // $beratKolom1 = 0;
                            // if ($laporan->dryers->count() > 0) {
                            //     $beratKolom1 = $laporan->dryers->sum('total_netto');
                            // } else {
                            //     $beratKolom1 = $laporan->berat_dryer ?? 0;
                            // }
                            $beratKolom1 = $laporan->berat_langsir ?? 0;
                            // Hitung berat untuk kolom kedua
                            $beratKolom2 = $laporan->berat_penjualan ?? 0;
                        @endphp

                        <tr>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                {{ \Carbon\Carbon::parse($laporan->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                {{ $laporan->kode }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                @if ($laporan->dryers->count() > 0)
                                    @foreach ($laporan->dryers as $index => $dryer)
                                        {{ $dryer->no_dryer ?? $dryer->no_dryer }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    {{ $laporan->lumbung ?? '-' }}
                                @endif
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{-- @if ($laporan->dryers->count() > 0)
                                    {{ number_format($laporan->dryers->sum('total_netto'), 0, ',', '.') }}
                                @else
                                    {{ $laporan->berat_dryer ? number_format($laporan->berat_dryer) : '-' }}
                                @endif --}}
                                @if (!empty($laporan->berat_langsir) && $laporan->berat_langsir > 0)
                                    {{ number_format($laporan->berat_langsir, 0, ',', '.') }}
                                @else
                                    {{ number_format($laporan->hasil ?? 0, 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data laporan lumbung
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="3" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            Total Berat:
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ number_format($totalBerat1, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>


        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>
        {{-- Tabel 2: Timbangan Trontons --}}
        @php
            // Fungsi untuk mengambil netto berdasarkan nama lumbung
            function getNettoBySiloName($timbangan, $siloName)
            {
                $totalNetto = 0;

                // Array untuk menyimpan field penjualan yang akan dicek
                $penjualanFields = ['penjualan1', 'penjualan2', 'penjualan3', 'penjualan4', 'penjualan5', 'penjualan6'];

                foreach ($penjualanFields as $field) {
                    // Cek apakah field penjualan ada dan nama lumbungnya sesuai
                    if (isset($timbangan->$field) && $timbangan->$field) {
                        // Asumsikan struktur penjualan memiliki field 'nama_lumbung' dan 'netto'
                        // Sesuaikan dengan struktur data Anda
                        if ($timbangan->$field->nama_lumbung == $siloName) {
                            $totalNetto += $timbangan->$field->netto ?? 0;
                        }
                    }
                }

                return $totalNetto;
            }

            // Hitung total berat yang sudah difilter
            $totalBeratTrontonFiltered = 0;
        @endphp

        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Laporan Penjualan</h3>
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Total Netto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($silo->timbanganTrontons as $timbangan)
                        @php
                            // Ambil netto yang sesuai dengan nama silo
                            $nettoFiltered = getNettoBySiloName($timbangan, $silo->nama);
                            $totalBeratTrontonFiltered += $nettoFiltered;
                        @endphp

                        @if ($nettoFiltered > 0)
                            <tr>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ \Carbon\Carbon::parse($timbangan->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $timbangan->kode }}
                                </td>
                                <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                    {{ number_format($nettoFiltered, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="3"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data timbangan tronton
                            </td>
                        </tr>
                    @endforelse

                    @if ($totalBeratTrontonFiltered == 0 && $silo->timbanganTrontons->count() > 0)
                        <tr>
                            <td colspan="3"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada penjualan yang sesuai dengan lumbung "{{ $silo->nama }}"
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="2" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            Total Berat:
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ number_format($totalBeratTrontonFiltered, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</x-filament-panels::page>
