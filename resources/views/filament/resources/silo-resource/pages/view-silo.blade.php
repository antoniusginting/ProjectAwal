<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        @php
            // Cek apakah silo nama adalah STAFFEL A atau STAFFEL B
            $isStaffelView = in_array($silo->nama, ['SILO STAFFEL A', 'SILO STAFFEL B', 'SILO 2500', 'SILO 1800']);

            // Ambil data penjualan langsung berdasarkan silo_id
            $penjualanFiltered = collect();

            // Ambil semua penjualan yang memiliki silo_id sesuai dengan silo saat ini
            if ($silo->penjualans) {
                $penjualanFiltered = $silo->penjualans;
            }

            // Hitung total berat dari penjualan yang sudah difilter
            $totalBeratPenjualanFiltered = $penjualanFiltered->sum('netto');

            // Data untuk pagination
            $laporanLumbungTotal = $silo->laporanlumbungs->count();
            $laporanPenjualanTotal = $penjualanFiltered->count();

            // Hitung total berat dari laporan lumbung berdasarkan total netto transferMasuk
            $totalBerat1 = 0;
            foreach ($silo->laporanlumbungs as $laporan) {
                // Hitung total netto dari transferMasuk yang terkait
                $totalNettoTransferMasuk = 0;
                if ($laporan->transferMasuk && $laporan->transferMasuk->count() > 0) {
                    $totalNettoTransferMasuk = $laporan->transferMasuk->sum('netto');
                }

                // Prioritas: transferMasuk > hasil
                if ($totalNettoTransferMasuk > 0) {
                    $totalBerat1 += $totalNettoTransferMasuk;
                }
                // Jika tidak ada transferMasuk, gunakan hasil sebagai fallback
                else {
                    $totalBerat1 += $laporan->hasil ?? 0;
                }
            }

            // Jika tidak ada total berat dari laporan lumbung, gunakan stock luar
            // if ($totalBerat1 == 0) {
            //     $totalBerat1 = $silo->stockLuar()->sum('quantity');
            // }

            // Hitung summary
            $totalStokDanBerat = $silo->stok + $totalBerat1;
            $stokSisa = $totalStokDanBerat - $totalBeratPenjualanFiltered;
            $persenan = $totalStokDanBerat != 0 ? ($totalBeratPenjualanFiltered / $totalStokDanBerat) * 100 : 0;

        @endphp

        @if ($isStaffelView)
            {{-- Summary Dashboard - Layout 1 Baris --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg mb-6 shadow-md border">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Ringkasan Stok {{ $silo->nama }}
                    </h3>
                </div>

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
                            {{ number_format($totalBeratPenjualanFiltered, 0, ',', '.') }}
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
                    @if ($silo->status)
                        <div
                            class="flex-1 text-center p-3 {{ $persenan >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Persenan</p>
                            <p
                                class="text-xl font-bold {{ $persenan >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($persenan, 2) }} %
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            <!-- Divider -->
            <div class="border-b border-gray-300 dark:border-gray-700"></div>

            {{-- Tabel 1: Laporan Lumbung --}}
            <div class="mb-6" id="laporan-lumbung">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold">Laporan Lumbung</h3>

                    </div>
                    {{-- To Bottom Button --}}
                    <button onclick="scrollToLaporanPenjualan()"
                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md transition-colors duration-200 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                        Ke Laporan Penjualan
                    </button>
                </div>
                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No IO</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Transfer</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                        </tr>
                    </thead>
                    <tbody id="lumbung-tbody">
                        @foreach ($silo->laporanlumbungs as $index => $laporan)
                            @php
                                $beratKolom1 = $laporan->berat_langsir ?? 0;
                                $beratKolom2 = $laporan->berat_penjualan ?? 0;
                            @endphp

                            <tr class="lumbung-row {{ $index >= 5 ? 'hidden' : '' }}"
                                data-index="{{ $index }}">
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ \Carbon\Carbon::parse($laporan->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    <a href="{{ route('filament.admin.resources.laporan-lumbungs.view-laporan-lumbung', $laporan->id ?? '') }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline">{{ $laporan->kode }}</a>
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    @if ($laporan->transferMasuk->count() > 0)
                                        @foreach ($laporan->transferMasuk as $index => $transfer)
                                            <a href="{{ route('filament.admin.resources.transfers.view-transfer', $transfer->id ?? '') }}"
                                                target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                                {{ $transfer->kode }}
                                            </a>{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                    @php
                                        $totalNettoDryers = 0;
                                        if ($laporan->dryers && $laporan->dryers->count() > 0) {
                                            $totalNettoDryers = $laporan->dryers->sum('netto');
                                        }

                                        $totalNettoTransfers = 0;
                                        if ($laporan->transfers && $laporan->transfers->count() > 0) {
                                            $totalNettoTransfers = $laporan->transfers->sum('netto');
                                        }

                                        $totalNettoTransferMasuk = 0;
                                        if ($laporan->transferMasuk && $laporan->transferMasuk->count() > 0) {
                                            $totalNettoTransferMasuk = $laporan->transferMasuk->sum('netto');
                                        }
                                    @endphp

                                    @if ($totalNettoDryers > 0)
                                        {{ number_format($totalNettoDryers, 0, ',', '.') }}
                                    @elseif ($totalNettoTransfers > 0)
                                        {{ number_format($totalNettoTransfers, 0, ',', '.') }}
                                    @elseif ($totalNettoTransferMasuk > 0)
                                        {{ number_format($totalNettoTransferMasuk, 0, ',', '.') }}
                                    @else
                                        {{ number_format($laporan->hasil ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if ($silo->laporanlumbungs->count() == 0)
                            <tr>
                                <td colspan="4"
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                    Tidak ada data laporan lumbung
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="3"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                Total Berat:
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($totalBerat1, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Dropdown untuk memilih jumlah data --}}
                <div class="mt-3 flex justify-center">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
                        <select id="lumbung-per-page" onchange="changeLumbungPerPage()"
                            class="px-6 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="5">5</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="all">Semua</option>
                        </select>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                            <span id="showing-lumbung">5</span>
                            dari {{ $laporanLumbungTotal }} data
                        </span>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-b border-gray-300 dark:border-gray-700"></div>

            {{-- Tabel 2: Data Penjualan --}}
            <div class="mb-6" id="laporan-penjualan">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold">Laporan Penjualan</h3>

                    </div>
                    {{-- Back to Top Button --}}
                    <button onclick="scrollToTop()"
                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md transition-colors duration-200 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        Ke Laporan Lumbung
                    </button>
                </div>
                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode</th>
                            {{-- <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Lumbung</th> --}}
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                        </tr>
                    </thead>
                    <tbody id="penjualan-tbody">
                        @php $penjualanIndex = 0; @endphp
                        @forelse($penjualanFiltered as $penjualan)
                            <tr class="penjualan-row {{ $penjualanIndex >= 5 ? 'hidden' : '' }}"
                                data-index="{{ $penjualanIndex }}">
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ \Carbon\Carbon::parse($penjualan->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    <a href="{{ route('filament.admin.resources.penjualans.view-penjualan', $penjualan->id ?? '') }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline">{{ $penjualan->no_spb ?? '-' }}
                                    </a>
                                </td>
                                {{-- <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $penjualan->nama ?? ($penjualan->nama_lumbung ?? ($penjualan->lumbung ?? '-')) }}
                                </td> --}}
                                <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                    {{ number_format($penjualan->netto ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @php $penjualanIndex++; @endphp
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                    Tidak ada data penjualan yang sesuai dengan lumbung "{{ $silo->nama }}"
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="2"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                Total Berat:
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($totalBeratPenjualanFiltered, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Dropdown untuk memilih jumlah data --}}
                <div class="mt-3 flex justify-center">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
                        <select id="penjualan-per-page" onchange="changePenjualanPerPage()"
                            class="px-6 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="5">5</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="all">Semua</option>
                        </select>
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                            <span id="showing-penjualan">5</span>
                            dari {{ $laporanPenjualanTotal }} data
                        </span>
                    </div>
                </div>
            </div>


            {{-- TABEL JIKA LUAR --}}
        @else
            {{-- Summary Dashboard - Layout 1 Baris --}}
            {{-- VIEW UNTUK TABEL LUAR --}}
            {{-- VIEW UNTUK TABEL LUAR --}}
            {{-- JavaScript untuk pagination --}}

            {{-- 
            <script>
                function showSupplierDebug() {
                    const debugDiv = document.getElementById('supplier-debug');
                    debugDiv.classList.toggle('hidden');
                }
            </script> --}}
        @endif
    </div>

    {{-- JavaScript untuk Scroll Functions dan Dropdown Pagination --}}
    <script>
        function scrollToLaporanPenjualan() {
            const element = document.getElementById('laporan-penjualan');
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function scrollToLaporanLumbung() {
            const element = document.getElementById('laporan-lumbung');
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Fungsi untuk mengubah jumlah data yang ditampilkan pada Laporan Lumbung
        function changeLumbungPerPage() {
            const select = document.getElementById('lumbung-per-page');
            const selectedValue = select.value;
            const rows = document.querySelectorAll('.lumbung-row');
            const showingCount = document.getElementById('showing-lumbung');

            let limit = selectedValue === 'all' ? rows.length : parseInt(selectedValue);
            let visibleCount = 0;

            rows.forEach((row, index) => {
                if (selectedValue === 'all' || index < limit) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            showingCount.textContent = visibleCount;
        }

        // Fungsi untuk mengubah jumlah data yang ditampilkan pada Laporan Penjualan
        function changePenjualanPerPage() {
            const select = document.getElementById('penjualan-per-page');
            const selectedValue = select.value;
            const rows = document.querySelectorAll('.penjualan-row');
            const showingCount = document.getElementById('showing-penjualan');

            let limit = selectedValue === 'all' ? rows.length : parseInt(selectedValue);
            let visibleCount = 0;

            rows.forEach((row, index) => {
                if (selectedValue === 'all' || index < limit) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            showingCount.textContent = visibleCount;
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial count for showing data
            const lumbungRows = document.querySelectorAll('.lumbung-row:not(.hidden)');
            const penjualanRows = document.querySelectorAll('.penjualan-row:not(.hidden)');

            document.getElementById('showing-lumbung').textContent = lumbungRows.length;
            document.getElementById('showing-penjualan').textContent = penjualanRows.length;
        });
    </script>
</x-filament-panels::page>
