<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md">

        <!-- Filter Section -->
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg mb-6 border">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Filter Data Kontrak</h3>

            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Filter Tanggal Mulai -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal Mulai
                    </label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filter Tanggal Selesai -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal Selesai
                    </label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                        value="{{ request('tanggal_selesai') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Hidden input untuk memastikan form submitted -->
                <input type="hidden" name="apply_filter" value="1">

                <!-- Tombol Filter dan Reset -->
                <div>
                    <button type="submit"
                        style="padding: 6px 12px; background-color: #2563eb; color: white; border: none; border-radius: 4px;">
                        Tampilkan Data
                    </button>
                    <a href="{{ request()->url() }}"
                        style="padding: 6px 12px; background-color: #6b7280; color: white; border-radius: 4px; text-decoration: none; margin-left: 8px;">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Info Filter Aktif -->
            @if (request()->hasAny(['tanggal_mulai', 'tanggal_selesai']))
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Filter Aktif:</strong>
                        @if (request('tanggal_mulai'))
                            Dari: {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }}
                        @endif
                        @if (request('tanggal_selesai'))
                            Sampai: {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
            @endif
        </div>

        @php
            // Cek apakah ada filter yang aktif - harus ada apply_filter dan minimal satu filter tanggal
            $hasFilter = request()->has('apply_filter') && request()->hasAny(['tanggal_mulai', 'tanggal_selesai']);

            // Inisialisasi variabel default
            $filteredKontrakBelis = collect();
            $totalKeseluruhanPembelian = 0;
            $totalStokKeseluruhan = 0;
            $totalSisaKontrak = 0;
            $persenTotalPembelian = 0;

            if ($hasFilter) {
                // Filter kontrak berdasarkan tanggal pembelian - hanya tampilkan kontrak yang memiliki pembelian dalam rentang tanggal
                $filteredKontrakBelis = $kontrakBelis->filter(function ($kontrak) {
                    if (!$kontrak->pembelianLuar || $kontrak->pembelianLuar->count() == 0) {
                        return false; // Skip kontrak yang tidak memiliki pembelian
                    }

                    // Cek apakah kontrak memiliki pembelian dalam rentang tanggal yang dipilih
                    $hasPembelianInRange =
                        $kontrak->pembelianLuar
                            ->filter(function ($pembelian) {
                                $tanggalPembelian = \Carbon\Carbon::parse($pembelian->created_at);

                                if (request('tanggal_mulai') && request('tanggal_selesai')) {
                                    return $tanggalPembelian->between(
                                        \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfDay(),
                                        \Carbon\Carbon::parse(request('tanggal_selesai'))->endOfDay(),
                                    );
                                } elseif (request('tanggal_mulai')) {
                                    return $tanggalPembelian->gte(
                                        \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfDay(),
                                    );
                                } elseif (request('tanggal_selesai')) {
                                    return $tanggalPembelian->lte(
                                        \Carbon\Carbon::parse(request('tanggal_selesai'))->endOfDay(),
                                    );
                                }

                                return true;
                            })
                            ->count() > 0;

                    return $hasPembelianInRange;
                });

                // Hitung total keseluruhan pembelian dari kontrak yang sudah difilter
                foreach ($filteredKontrakBelis as $kontrak) {
                    if ($kontrak->pembelianLuar) {
                        $pembelianFiltered = $kontrak->pembelianLuar->filter(function ($pembelian) {
                            $tanggalPembelian = \Carbon\Carbon::parse($pembelian->created_at);

                            if (request('tanggal_mulai') && request('tanggal_selesai')) {
                                return $tanggalPembelian->between(
                                    \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfDay(),
                                    \Carbon\Carbon::parse(request('tanggal_selesai'))->endOfDay(),
                                );
                            } elseif (request('tanggal_mulai')) {
                                return $tanggalPembelian->gte(
                                    \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfDay(),
                                );
                            } elseif (request('tanggal_selesai')) {
                                return $tanggalPembelian->lte(
                                    \Carbon\Carbon::parse(request('tanggal_selesai'))->endOfDay(),
                                );
                            }

                            return true;
                        });

                        $totalKeseluruhanPembelian += $pembelianFiltered->sum('netto');
                    }
                }

                // Hitung total sisa kontrak keseluruhan
                $totalStokKeseluruhan = $filteredKontrakBelis->sum('stok');
                $totalSisaKontrak = $totalStokKeseluruhan - $totalKeseluruhanPembelian;
                $persenTotalPembelian =
                    $totalStokKeseluruhan != 0 ? ($totalKeseluruhanPembelian / $totalStokKeseluruhan) * 100 : 0;
            }
        @endphp

        @if (!$hasFilter)
            <!-- Pesan ketika belum ada filter -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-6 rounded-lg text-center">
                <div class="mb-4">
                    {{-- <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg> --}}
                </div>
                <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                    Silakan Pilih Filter Tanggal Terlebih Dahulu
                </h3>
                <p class="text-yellow-700 dark:text-yellow-300 mb-4">
                    Gunakan form filter di atas untuk menampilkan data laporan kontrak dan pembelian.
                    Pilih tanggal mulai dan/atau tanggal selesai, kemudian klik tombol <strong>"Tampilkan
                        Data"</strong>.
                </p>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Tips Penggunaan:</strong></p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Pilih tanggal mulai dan/atau tanggal selesai untuk filter berdasarkan periode</li>
                        <li>Sistem akan menampilkan kontrak yang memiliki pembelian dalam rentang tanggal tersebut</li>
                        <li>Gunakan tombol "Reset" untuk menghapus semua filter</li>
                    </ul>
                </div>
            </div>
        @elseif ($filteredKontrakBelis->count() == 0)
            <!-- Pesan ketika tidak ada kontrak dalam rentang tanggal -->
            <div class="bg-orange-50 dark:bg-orange-900/20 p-6 rounded-lg text-center">
                <div class="mb-4">
                    {{-- <svg class="mx-auto h-12 w-12 text-orange-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.25 0-4.31.954-5.824 2.491">
                        </path>
                    </svg> --}}
                </div>
                <h3 class="text-lg font-medium text-orange-800 dark:text-orange-200 mb-2">
                    Tidak Ada Data Kontrak
                </h3>
                <p class="text-orange-700 dark:text-orange-300 mb-4">
                    Tidak ditemukan kontrak yang memiliki pembelian dalam rentang tanggal yang dipilih
                    @if (request('tanggal_mulai') && request('tanggal_selesai'))
                        ({{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}).
                    @elseif (request('tanggal_mulai'))
                        (mulai {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }}).
                    @elseif (request('tanggal_selesai'))
                        (sampai {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d/m/Y') }}).
                    @endif
                </p>
                <p class="text-sm text-orange-600 dark:text-orange-400">
                    Silakan coba dengan rentang tanggal yang berbeda atau periksa data pembelian Anda.
                </p>
            </div>
        @else
            <!-- Summary Cards - hanya tampil jika ada filter dan ada data -->
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Kontrak -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Kontrak</p>
                        <p class="text-xl font-bold text-blue-600">{{ $filteredKontrakBelis->count() }}</p>
                    </div>

                    <!-- Total Stok -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Stok Kontrak</p>
                        <p class="text-xl font-bold text-indigo-600">
                            {{ number_format($totalStokKeseluruhan, 0, ',', '.') }}</p>
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
                        <p
                            class="text-xl font-bold {{ $totalSisaKontrak >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($totalSisaKontrak, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detail setiap kontrak -->
            <div class="space-y-8">
                @foreach ($filteredKontrakBelis as $kontrakBeli)
                    <div
                        class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200 border-l-4 border-green-500">
                        @php
                            // Filter pembelian berdasarkan tanggal yang dipilih
                            $pembelianFiltered = $kontrakBeli->pembelianLuar->filter(function ($pembelian) {
                                $tanggalPembelian = \Carbon\Carbon::parse($pembelian->created_at);

                                if (request('tanggal_mulai') && request('tanggal_selesai')) {
                                    return $tanggalPembelian->between(
                                        \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfDay(),
                                        \Carbon\Carbon::parse(request('tanggal_selesai'))->endOfDay(),
                                    );
                                } elseif (request('tanggal_mulai')) {
                                    return $tanggalPembelian->gte(
                                        \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfDay(),
                                    );
                                } elseif (request('tanggal_selesai')) {
                                    return $tanggalPembelian->lte(
                                        \Carbon\Carbon::parse(request('tanggal_selesai'))->endOfDay(),
                                    );
                                }

                                return true;
                            });

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
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    Ringkasan Tonase Kontrak {{ $kontrakBeli->supplier }} - {{ $kontrakBeli->nama }}
                                    <span class="text-sm text-blue-600 dark:text-blue-400 font-normal">
                                        (Data Periode Terpilih)
                                    </span>
                                </h3>
                            </div>

                            {{-- Data Stok & Pembelian --}}
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Data Tonase
                                    Kontrak & Pembelian</h4>
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
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Tanggal
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode</th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode Segel
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Barang
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No
                                            Container</th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Ekspedisi
                                        </th>
                                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                                    </tr>
                                </thead>
                                <tbody id="pembelian-tbody-{{ $kontrakBeli->id }}">
                                    @php $pembelianIndex = 0; @endphp
                                    @foreach ($pembelianFiltered as $pembelian)
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
                                    @endforeach
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
                            @if ($laporanPembelianTotal > 0)
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
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
            @if ($hasFilter && $filteredKontrakBelis->count() > 0)
                @foreach ($filteredKontrakBelis as $kontrak)
                    const pembelianRows{{ $kontrak->id }} = document.querySelectorAll(
                        '.pembelian-row-{{ $kontrak->id }}:not(.hidden)');
                    const showingElement{{ $kontrak->id }} = document.getElementById(
                        'showing-pembelian-{{ $kontrak->id }}');
                    if (showingElement{{ $kontrak->id }}) {
                        showingElement{{ $kontrak->id }}.textContent = pembelianRows{{ $kontrak->id }}.length;
                    }
                @endforeach
            @endif
        });
    </script>
</x-filament-panels::page>
