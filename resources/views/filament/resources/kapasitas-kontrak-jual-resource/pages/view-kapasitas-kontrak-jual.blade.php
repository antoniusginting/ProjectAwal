<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        @php
            // Ambil data penjualan langsung berdasarkan kontrak_luar_id
            $penjualanFiltered = collect();
            $suratJalanFiltered = collect();

            // Ambil semua penjualan yang memiliki kontrak_luar_id sesuai dengan kontrak luar saat ini
            if ($kontrakLuar->penjualanLuar) {
                $penjualanFiltered = $kontrakLuar->penjualanLuar;
            }

            // Ambil semua surat jalan yang memiliki kontrak_luar_id sesuai dengan kontrak luar saat ini
            if ($kontrakLuar->suratJalan) {
                $suratJalanFiltered = $kontrakLuar->suratJalan;
            }

            // Hitung total berat dari penjualan yang sudah difilter
            $totalBeratPenjualanFiltered = $penjualanFiltered->sum('netto_diterima');
            $totalBeratSuratJalanFiltered = $suratJalanFiltered->sum('netto_diterima');
            $totalBeratKeseluruhan = $totalBeratPenjualanFiltered + $totalBeratSuratJalanFiltered;

            // Data untuk pagination
            $laporanPenjualanTotal = $penjualanFiltered->count();
            $laporanSuratJalanTotal = $suratJalanFiltered->count();
            $laporanKeseluruhanTotal = $laporanPenjualanTotal + $laporanSuratJalanTotal;

            // Hitung summary
            $totalStokDanBerat = $kontrakLuar->stok;
            $stokSisa = $totalStokDanBerat - $totalBeratKeseluruhan;
            $persenanPenjualan = $totalStokDanBerat != 0 ? ($totalBeratKeseluruhan / $totalStokDanBerat) * 100 : 0;
        @endphp

        {{-- Summary Dashboard --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg mb-6 shadow-md border">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Ringkasan Stok
                    {{ $kontrakLuar->nama }}</h3>
            </div>

            {{-- Data Stok & Penjualan --}}
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Data Stok & Penjualan</h4>
                <div class="flex flex-row gap-4">
                    <!-- Stok Awal -->
                    <div class="flex-1 text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Stok Awal</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($kontrakLuar->stok, 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Total Penjualan Langsung -->
                    <div class="flex-1 text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Penjualan Langsung</p>
                        <p class="text-xl font-bold text-orange-600 dark:text-orange-400">
                            {{ number_format($totalBeratPenjualanFiltered, 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Total Surat Jalan -->
                    <div class="flex-1 text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Surat Jalan</p>
                        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ number_format($totalBeratSuratJalanFiltered, 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Total Keseluruhan -->
                    <div class="flex-1 text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Penjualan</p>
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ number_format($totalBeratKeseluruhan, 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Sisa Stok -->
                    <div
                        class="flex-1 text-center p-3 {{ $stokSisa >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Sisa Stok</p>
                        <p
                            class="text-xl font-bold {{ $stokSisa >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($stokSisa, 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Persenan Penjualan -->
                    @if ($kontrakLuar->status)
                        <div class="flex-1 text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">% Penjualan</p>
                            <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                                {{ number_format($persenanPenjualan, 2) }} %
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        {{-- Tabel: Data Penjualan Langsung --}}
        <div class="mb-6" id="laporan-penjualan">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold">Laporan Penjualan Langsung</h3>
                </div>
            </div>
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode Segel</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Barang</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Container</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Status</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto Diterima</th>
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
                                <a>{{ $penjualan->kode ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $penjualan->kode_segel ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $penjualan->nama_barang ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $penjualan->no_container ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $penjualan->status ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($penjualan->netto ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($penjualan->netto_diterima ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @php $penjualanIndex++; @endphp
                    @empty
                        <tr>
                            <td colspan="8"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data penjualan langsung yang sesuai dengan kontrak "{{ $kontrakLuar->nama }}"
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="7" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            Total Berat Penjualan Langsung:
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ number_format($totalBeratPenjualanFiltered, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- Dropdown untuk memilih jumlah data penjualan --}}
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

        {{-- Tabel: Data Surat Jalan --}}
        <div class="mb-6" id="laporan-suratjalan">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold">Laporan Surat Jalan</h3>
                </div>
            </div>
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Penjualan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Plat Polisi</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No PO</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Supir</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Status</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto Diterima</th>
                    </tr>
                </thead>
                <tbody id="suratjalan-tbody">
                    @php $suratJalanIndex = 0; @endphp
                    @forelse($suratJalanFiltered as $suratJalan)
                        <tr class="suratjalan-row {{ $suratJalanIndex >= 5 ? 'hidden' : '' }}"
                            data-index="{{ $suratJalanIndex }}">
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                {{ \Carbon\Carbon::parse($suratJalan->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $suratJalan->timbanganTronton->kode ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $suratJalan->timbanganTronton->penjualan1->plat_polisi ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $suratJalan->po ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $suratJalan->timbanganTronton->penjualan1->nama_supir ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                <a>{{ $suratJalan->status ?? '-' }}</a>
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($suratJalan->netto_final ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($suratJalan->netto_diterima ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @php $suratJalanIndex++; @endphp
                    @empty
                        <tr>
                            <td colspan="8"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data surat jalan yang sesuai dengan kontrak "{{ $kontrakLuar->nama }}"
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="7" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            Total Berat Surat Jalan:
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ number_format($totalBeratSuratJalanFiltered, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- Dropdown untuk memilih jumlah data surat jalan --}}
            <div class="mt-3 flex justify-center">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
                    <select id="suratjalan-per-page" onchange="changeSuratJalanPerPage()"
                        class="px-6 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="5">5</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="all">Semua</option>
                    </select>
                    <span
                        class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                        <span id="showing-suratjalan">5</span>
                        dari {{ $laporanSuratJalanTotal }} data
                    </span>
                </div>
            </div>
        </div>

        {{-- Total Keseluruhan --}}
        {{-- <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Total Keseluruhan</h3>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Berat Keseluruhan (Penjualan + Surat
                        Jalan)</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ number_format($totalBeratKeseluruhan, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div> --}}
    </div>

    {{-- JavaScript untuk Scroll Functions dan Dropdown Pagination --}}
    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
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

        // Fungsi untuk mengubah jumlah data yang ditampilkan pada Laporan Surat Jalan
        function changeSuratJalanPerPage() {
            const select = document.getElementById('suratjalan-per-page');
            const selectedValue = select.value;
            const rows = document.querySelectorAll('.suratjalan-row');
            const showingCount = document.getElementById('showing-suratjalan');

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
            // Set initial count for showing data penjualan
            const penjualanRows = document.querySelectorAll('.penjualan-row:not(.hidden)');
            document.getElementById('showing-penjualan').textContent = penjualanRows.length;

            // Set initial count for showing data surat jalan
            const suratJalanRows = document.querySelectorAll('.suratjalan-row:not(.hidden)');
            document.getElementById('showing-suratjalan').textContent = suratJalanRows.length;
        });
    </script>
</x-filament-panels::page>
