<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        @php
            // Ambil data penjualan langsung berdasarkan luar_pulau_id
            $penjualanFiltered = collect();

            // Ambil semua penjualan yang memiliki luar_pulau_id sesuai dengan luar pulau saat ini
            if ($luarPulau->luars) {
                $penjualanFiltered = $luarPulau->luars;
            }

            // Hitung total berat dari penjualan yang sudah difilter
            $totalBeratPenjualanFiltered = $penjualanFiltered->sum('netto');

            // Data untuk pagination
            $laporanPenjualanTotal = $penjualanFiltered->count();

            // Hitung summary
            $totalStokDanBerat = $luarPulau->stok;
            $stokSisa = $totalStokDanBerat - $totalBeratPenjualanFiltered;
            $persenan = $totalStokDanBerat != 0 ? ($totalBeratPenjualanFiltered / $totalStokDanBerat) * 100 : 0;

        @endphp

        {{-- Summary Dashboard - Layout 1 Baris --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg mb-6 shadow-md border">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Ringkasan Stok {{ $luarPulau->nama }}
                </h3>
            </div>

            <div class="flex flex-row gap-4">
                <!-- Stok Awal -->
                <div class="flex-1 text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Stok Awal</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($luarPulau->stok, 0, ',', '.') }}
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
                @if ($luarPulau->status)
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

        {{-- Tabel: Data Penjualan --}}
        <div class="mb-6" id="laporan-penjualan">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold">Laporan Pembelian</h3>
                </div>
                {{-- Back to Top Button --}}
                {{-- <button onclick="scrollToTop()"
                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md transition-colors duration-200 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    Ke Atas
                </button> --}}
            </div>
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode</th>
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
                                <a >{{ $penjualan->kode ?? '-' }}
                                </a>
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($penjualan->netto ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @php $penjualanIndex++; @endphp
                    @empty
                        <tr>
                            <td colspan="3"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data penjualan yang sesuai dengan supplier "{{ $luarPulau->nama }}"
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="2" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
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

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial count for showing data
            const penjualanRows = document.querySelectorAll('.penjualan-row:not(.hidden)');
            document.getElementById('showing-penjualan').textContent = penjualanRows.length;
        });
    </script>
</x-filament-panels::page>
