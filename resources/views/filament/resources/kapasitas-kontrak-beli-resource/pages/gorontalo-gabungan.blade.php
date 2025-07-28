<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md">
{{-- 
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg mb-6">
            <p class="text-lg">Total Supplier: <strong>{{ $kontrakBelis->count() }}</strong></p>
            <p class="text-lg">Total Nilai Kontrak:
                <strong>{{ number_format($kontrakBelis->sum('stok'), 0, ',', '.') }}</strong>
            </p>
        </div> --}}

        @php
            // Hitung total keseluruhan pembelian dari semua kontrak
            $totalKeseluruhanPembelian = 0;
            foreach ($kontrakBelis as $kontrak) {
                if ($kontrak->pembelianLuar) {
                    $totalKeseluruhanPembelian += $kontrak->pembelianLuar->sum('netto');
                }
            }

            // Hitung total sisa kontrak keseluruhan
            $totalStokKeseluruhan = $kontrakBelis->sum('stok');
            $totalSisaKontrak = $totalStokKeseluruhan - $totalKeseluruhanPembelian;
            $persenTotalPembelian =
                $totalStokKeseluruhan != 0 ? ($totalKeseluruhanPembelian / $totalStokKeseluruhan) * 100 : 0;
        @endphp

        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Kontrak -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Kontrak</p>
                    <p class="text-xl font-bold text-blue-600">{{ $kontrakBelis->count() }}</p>
                </div>

                <!-- Total Stok -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Stok Kontrak</p>
                    <p class="text-xl font-bold text-indigo-600">{{ number_format($totalStokKeseluruhan, 0, ',', '.') }}
                    </p>
                </div>

                <!-- Total Pembelian -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Pembelian</p>
                    <p class="text-xl font-bold text-green-600">
                        {{ number_format($totalKeseluruhanPembelian, 0, ',', '.') }}</p>
                </div>

                <!-- Total Sisa Kontrak -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Sisa Kontrak</p>
                    <p class="text-xl font-bold {{ $totalSisaKontrak >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ number_format($totalSisaKontrak, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto mb-8">
                {{-- <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700">No</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Supplier</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Stok</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Harga</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kontrakBelis as $index => $kontrak)
                        <tr>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">{{ $index + 1 }}
                            </td>
                            <td class="border p-2 border-gray-300 dark:border-gray-700">{{ $kontrak->supplier }}</td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700">
                                {{ number_format($kontrak->stok, 0, ',', '.') }}</td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700">
                                {{ number_format($kontrak->harga, 0, ',', '.') }}</td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700">
                                <span
                                    class="px-2 py-1 rounded text-sm {{ $kontrak->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $kontrak->status ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table> --}}
            </div>

            <!-- Detail setiap kontrak -->
            <div class="space-y-8">
                {{-- <h3 class="text-xl font-bold text-green-600 border-b-2 border-green-200 pb-2">Detail Setiap Kontrak</h3> --}}

                @foreach ($kontrakBelis as $kontrakBeli)
                    <div
                        class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200 border-l-4 border-green-500">
                        @php
                            // Ambil data pembelian langsung berdasarkan kontrak
                            $pembelianFiltered = collect();

                            // Ambil semua pembelian yang memiliki relasi dengan kontrak saat ini
                            if ($kontrakBeli->pembelianLuar) {
                                $pembelianFiltered = $kontrakBeli->pembelianLuar;
                            }

                            // Hitung total berat dari pembelian yang sudah difilter
                            $totalBeratPembelianFiltered = $pembelianFiltered->sum('netto');

                            // Data untuk pagination
                            $laporanPembelianTotal = $pembelianFiltered->count();

                            // Hitung summary
                            $totalStokDanBerat = $kontrakBeli->stok;
                            $stokSisa = $totalStokDanBerat - $totalBeratPembelianFiltered;
                            $persenanPembelian =
                                $totalStokDanBerat != 0 ? ($totalBeratPembelianFiltered / $totalStokDanBerat) * 100 : 0;
                        @endphp

                        {{-- Summary Dashboard --}}
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg mb-6 shadow-md border">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Ringkasan Tonase
                                    Kontrak
                                    {{ $kontrakBeli->supplier }} - {{ $kontrakBeli->nama }}
                                </h3>
                            </div>

                            {{-- Data Stok & Pembelian --}}
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Data Tonase
                                    Kontrak &
                                    Pembelian
                                </h4>
                                <div class="flex flex-row gap-4">
                                    <!-- Stok Awal -->
                                    <div class="flex-1 text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Tonase Kontrak</p>
                                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ number_format($kontrakBeli->stok, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <!-- Total Pembelian -->
                                    <div class="flex-1 text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Pembelian</p>
                                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                            {{ number_format($totalBeratPembelianFiltered, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <!-- Sisa Stok -->
                                    <div
                                        class="flex-1 text-center p-3 {{ $stokSisa >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Sisa kontrak</p>
                                        <p
                                            class="text-xl font-bold {{ $stokSisa >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($stokSisa, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="flex-1 text-center p-3 rounded-lg">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Harga</p>
                                        <p class="text-xl font-bold">
                                            {{ number_format($kontrakBeli->harga, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <!-- Persenan Pembelian -->
                                    @if ($kontrakBeli->status)
                                        <div
                                            class="flex-1 text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">% Pembelian</p>
                                            <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                                                {{ number_format($persenanPembelian, 2) }} %
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-b border-gray-300 dark:border-gray-700"></div>

                        {{-- Tabel: Data Pembelian --}}
                        <div class="mb-6" id="laporan-pembelian-{{ $kontrakBeli->id }}">
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-semibold">Laporan Pembelian - {{ $kontrakBeli->supplier }}
                                    </h3>
                                </div>
                            </div>
                            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-800">
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal</th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode</th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode Segel
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Barang
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Container
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Ekspedisi
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                                    </tr>
                                </thead>
                                <tbody id="pembelian-tbody-{{ $kontrakBeli->id }}">
                                    @php $pembelianIndex = 0; @endphp
                                    @forelse($pembelianFiltered as $pembelian)
                                        <tr class="pembelian-row-{{ $kontrakBeli->id }} {{ $pembelianIndex >= 5 ? 'hidden' : '' }}"
                                            data-index="{{ $pembelianIndex }}">
                                            <td
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                                {{ \Carbon\Carbon::parse($pembelian->created_at)->format('d/m/Y') }}
                                            </td>
                                            <td
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                                <a>{{ $pembelian->kode ?? '-' }}</a>
                                            </td>
                                            <td
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                                <a>{{ $pembelian->kode_segel ?? '-' }}</a>
                                            </td>
                                            <td
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                                <a>{{ $pembelian->nama_barang ?? '-' }}</a>
                                            </td>
                                            <td
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                                <a>{{ $pembelian->no_container ?? '-' }}</a>
                                            </td>
                                            <td
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                                <a>{{ $pembelian->nama_ekspedisi ?? '-' }}</a>
                                            </td>
                                            <td
                                                class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                                {{ number_format($pembelian->netto ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @php $pembelianIndex++; @endphp
                                    @empty
                                        <tr>
                                            <td colspan="7"
                                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                                Tidak ada data pembelian untuk kontrak "{{ $kontrakBeli->supplier }}"
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                                        <td colspan="6"
                                            class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                            Total Berat:
                                        </td>
                                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                            {{ number_format($totalBeratPembelianFiltered, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- Dropdown untuk memilih jumlah data pembelian --}}
                            <div class="mt-3 flex justify-center">
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
                                    <select id="pembelian-per-page-{{ $kontrakBeli->id }}"
                                        onchange="changePembelianPerPage({{ $kontrakBeli->id }})"
                                        class="px-6 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="5">5</option>
                                        <option value="15">15</option>
                                        <option value="25">25</option>
                                        <option value="all">Semua</option>
                                    </select>
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                        <span
                                            id="showing-pembelian-{{ $kontrakBeli->id }}">{{ min(5, $laporanPembelianTotal) }}</span>
                                        dari {{ $laporanPembelianTotal }} data
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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

            // Fungsi untuk mengubah jumlah data yang ditampilkan pada Laporan Pembelian
            function changePembelianPerPage(kontrakId) {
                const select = document.getElementById('pembelian-per-page-' + kontrakId);
                const selectedValue = select.value;
                const rows = document.querySelectorAll('.pembelian-row-' + kontrakId);
                const showingCount = document.getElementById('showing-pembelian-' + kontrakId);

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
                // Set initial count for showing data untuk setiap kontrak
                @foreach ($kontrakBelis as $kontrak)
                    const pembelianRows{{ $kontrak->id }} = document.querySelectorAll(
                        '.pembelian-row-{{ $kontrak->id }}:not(.hidden)');
                    const showingElement{{ $kontrak->id }} = document.getElementById(
                        'showing-pembelian-{{ $kontrak->id }}');
                    if (showingElement{{ $kontrak->id }}) {
                        showingElement{{ $kontrak->id }}.textContent = pembelianRows{{ $kontrak->id }}.length;
                    }
                @endforeach
            });
        </script>
</x-filament-panels::page>
