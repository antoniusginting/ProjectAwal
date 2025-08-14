<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        @php
            // Ambil data penjualan langsung berdasarkan silo_id
            $penjualanFiltered = collect();

            // Ambil semua penjualan yang memiliki silo_id sesuai dengan silo saat ini
            if ($silo->penjualans) {
                $penjualanFiltered = $silo->penjualans;
            }

            // Tambahkan transfer keluar ke data penjualan
            $transferKeluarData = collect();
            if ($silo->transferKeluar) {
                foreach ($silo->transferKeluar as $transfer) {
                    $transferKeluarData->push([
                        'id' => $transfer->id,
                        'no_spb' => $transfer->kode,
                        'netto' => $transfer->netto,
                        'created_at' => $transfer->created_at,
                        'is_transfer' => true,
                        'silo_masuk_nama' => optional($transfer->siloMasuk)->nama ?? 'Tidak Diketahui',
                    ]);
                }
            }

            // Konversi penjualan ke array untuk konsistensi
            $penjualanArray = $penjualanFiltered->map(function ($penjualan) {
                return [
                    'id' => $penjualan->id,
                    'no_spb' => $penjualan->no_spb,
                    'netto' => $penjualan->netto,
                    'created_at' => $penjualan->created_at,
                    'is_transfer' => false,
                ];
            });

            // Gabungkan penjualan dengan transfer keluar
            $penjualanFiltered = $penjualanArray
                ->concat($transferKeluarData)
                ->sortByDesc('created_at')
                ->values();

            // Hitung total berat dari penjualan dan transfer keluar yang sudah difilter
            $totalBeratPenjualanFiltered = $penjualanFiltered->sum('netto');

            // Data untuk pagination
            $laporanLumbungTotal = $silo->laporanlumbungs->count();
            $laporanPenjualanTotal = $penjualanFiltered->count();

            // Hitung total berat dari laporan lumbung berdasarkan prioritas: dryer > transferMasuk > hasil
            $totalBerat1 = 0;
            foreach ($silo->laporanlumbungs as $laporan) {
                // Hitung total netto dari dryer yang terkait (menggunakan field total_netto)
                $totalNettoDryer = 0;
                if ($laporan->dryers && $laporan->dryers->count() > 0) {
                    $totalNettoDryer = $laporan->dryers->sum('total_netto');
                }

                // Hitung total netto dari transferMasuk yang terkait
                $totalNettoTransferMasuk = 0;
                if ($laporan->transferMasuk && $laporan->transferMasuk->count() > 0) {
                    $totalNettoTransferMasuk = $laporan->transferMasuk->sum('netto');
                }

                // Prioritas: dryer > transferMasuk > hasil
                if ($totalNettoDryer > 0) {
                    $totalBerat1 += $totalNettoDryer;
                }
                // Jika tidak ada dryer, gunakan transferMasuk
                elseif ($totalNettoTransferMasuk > 0) {
                    $totalBerat1 += $totalNettoTransferMasuk;
                }
                // Jika tidak ada dryer dan transferMasuk, gunakan hasil sebagai fallback
                else {
                    $totalBerat1 += $laporan->hasil ?? 0;
                }
            }

            // Tambahkan data langsir ke total berat
            $totalBeratLangsir = $silo->langsir->sum('netto');
            $totalBerat1 += $totalBeratLangsir;

            // Tambahkan transfer masuk langsung ke silo (bukan melalui laporan lumbung)
            $totalBeratTransferMasuk = $silo->transferMasuk->sum('netto');
            $totalBerat1 += $totalBeratTransferMasuk;

            // Hitung summary
            $totalStokDanBerat = $silo->stok + $totalBerat1;
            $stokSisa = $totalStokDanBerat - $totalBeratPenjualanFiltered;
            $persenan = $totalStokDanBerat != 0 ? ($totalBeratPenjualanFiltered / $totalStokDanBerat) * 100 : 0;

        @endphp
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
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No IO / No Transfer / No
                            SPB</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Dryer / Langsir /
                            Transfer
                        </th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    </tr>
                </thead>
                <tbody id="lumbung-tbody">
                    @php
                        // Gabungkan data laporan lumbung, langsir, dan transfer masuk untuk sorting berdasarkan tanggal
                        $allData = collect();

                        // Tambahkan data laporan lumbung
                        foreach ($silo->laporanlumbungs as $laporan) {
                            $allData->push([
                                'type' => 'laporan',
                                'data' => $laporan,
                                'date' => $laporan->created_at,
                            ]);
                        }

                        // Tambahkan data langsir
                        foreach ($silo->langsir as $langsir) {
                            $allData->push([
                                'type' => 'langsir',
                                'data' => $langsir,
                                'date' => $langsir->created_at,
                            ]);
                        }

                        // Tambahkan data transfer masuk
                        foreach ($silo->transferMasuk as $transferMasuk) {
                            // Cek apakah transfer ini sudah terkait dengan laporan lumbung
                            $isLinkedToLaporan = false;
                            foreach ($silo->laporanlumbungs as $laporan) {
                                if (
                                    $laporan->transferMasuk &&
                                    $laporan->transferMasuk->contains('id', $transferMasuk->id)
                                ) {
                                    $isLinkedToLaporan = true;
                                    break;
                                }
                            }

                            // Hanya tambahkan jika tidak terkait dengan laporan lumbung
                            if (!$isLinkedToLaporan) {
                                $allData->push([
                                    'type' => 'transfer_masuk',
                                    'data' => $transferMasuk,
                                    'date' => $transferMasuk->created_at,
                                ]);
                            }
                        }

                        // Sort berdasarkan tanggal (terbaru di atas)
                        $allData = $allData->sortByDesc('date');
                    @endphp

                    @foreach ($allData as $index => $item)
                        <tr class="lumbung-row {{ $index >= 5 ? 'hidden' : '' }}" data-index="{{ $index }}">
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                {{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                @if ($item['type'] === 'laporan')
                                    <a href="{{ route('filament.admin.resources.laporan-lumbungs.view-laporan-lumbung', $item['data']->id ?? '') }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline">{{ $item['data']->kode }}</a>
                                @elseif ($item['type'] === 'transfer_masuk')
                                    <a href="{{ route('filament.admin.resources.transfers.view-transfer', $item['data']->id ?? '') }}"
                                        target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800 underline">{{ $item['data']->kode }}</a>
                                @else
                                    @php
                                        $langsir = $item['data'];
                                    @endphp

                                    @if (
                                        $langsir->penjualan_id &&
                                            $langsir->penjualan &&
                                            $langsir->penjualan->laporan_lumbung_id &&
                                            $langsir->penjualan->laporanLumbung)
                                        <a href="{{ route('filament.admin.resources.laporan-lumbungs.view-laporan-lumbung', $langsir->penjualan->laporanLumbung->id) }}"
                                            target="_blank" class="text-blue-600 hover:text-blue-800 underline text-sm">
                                            {{ $langsir->penjualan->laporanLumbung->kode }}
                                        </a>
                                        -
                                    @endif

                                    <a href="{{ route('filament.admin.resources.transfers.view-transfer', $langsir->id ?? '') }}"
                                        target="_blank"
                                        class="text-purple-600 hover:text-purple-800 underline">{{ $langsir->kode }}</a>

                                    @if ($langsir->penjualan_id && $langsir->penjualan)
                                        -
                                        <a href="{{ route('filament.admin.resources.penjualans.view-penjualan', $langsir->penjualan->id) }}"
                                            target="_blank" class="text-blue-600 hover:text-blue-800 underline text-sm">
                                            {{ $langsir->penjualan->no_spb }}
                                        </a>
                                    @endif
                                @endif
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                @if ($item['type'] === 'laporan')
                                    @php
                                        $laporan = $item['data'];
                                        $hasContent = false;
                                    @endphp

                                    {{-- Tampilkan Dryers jika ada --}}
                                    @if ($laporan->dryers && $laporan->dryers->count() > 0)
                                        @foreach ($laporan->dryers as $index => $dryer)
                                            <a href="{{ route('filament.admin.resources.dryers.view-dryer', $dryer->id ?? '') }}"
                                                target="_blank" class="text-green-600 hover:text-green-800 underline">
                                                {{ $dryer->no_dryer }}
                                            </a>{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                        @php $hasContent = true; @endphp
                                    @endif

                                    {{-- Tambahkan separator jika ada dryer dan transfer --}}
                                    @if ($hasContent && $laporan->transferMasuk && $laporan->transferMasuk->count() > 0)
                                        <br>
                                    @endif

                                    {{-- Tampilkan Transfers --}}
                                    @if ($laporan->transferMasuk && $laporan->transferMasuk->count() > 0)
                                        @foreach ($laporan->transferMasuk as $index => $transfer)
                                            <a href="{{ route('filament.admin.resources.transfers.view-transfer', $transfer->id ?? '') }}"
                                                target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                                {{ $transfer->kode }}
                                            </a>{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                        @php $hasContent = true; @endphp
                                    @endif

                                    {{-- Jika tidak ada dryer atau transfer --}}
                                    @if (!$hasContent)
                                        -
                                    @endif
                                @elseif ($item['type'] === 'transfer_masuk')
                                    {{-- Tampilkan data transfer masuk --}}
                                    <div class="text-indigo-600 font-medium">
                                        <span>Transfer Masuk Dari</span>
                                        <a href="{{ route('filament.admin.resources.silos.view-silo', $item['data']->siloKeluar->id ?? '') }}"
                                            target="_blank"
                                            class="text-indigo-600 hover:text-indigo-800 underline">{{ $item['data']->siloKeluar->nama }}</a>
                                    </div>
                                @else
                                    {{-- Tampilkan data langsir --}}
                                    <div class="text-purple-600 font-medium">
                                        <span>Langsir</span>
                                    </div>
                                @endif

                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                @if ($item['type'] === 'laporan')
                                    @php
                                        $laporan = $item['data'];
                                        $totalNettoDryers = 0;
                                        if ($laporan->dryers && $laporan->dryers->count() > 0) {
                                            $totalNettoDryers = $laporan->dryers->sum('total_netto');
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
                                @elseif ($item['type'] === 'transfer_masuk')
                                    {{-- Tampilkan berat transfer masuk --}}
                                    {{ number_format($item['data']->netto ?? 0, 0, ',', '.') }}
                                @else
                                    {{-- Tampilkan berat langsir --}}
                                    {{ number_format($item['data']->netto ?? 0, 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if ($allData->count() == 0)
                        <tr>
                            <td colspan="4"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data laporan lumbung, langsir, atau transfer masuk
                            </td>
                        </tr>
                    @endif
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
                        dari {{ $allData->count() }} data
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
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode / No SPB</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Netto</th>
                    </tr>
                </thead>
                <tbody id="penjualan-tbody">
                    @php $penjualanIndex = 0; @endphp
                    @forelse($penjualanFiltered as $item)
                        <tr class="penjualan-row {{ $penjualanIndex >= 5 ? 'hidden' : '' }}"
                            data-index="{{ $penjualanIndex }}">
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                {{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y') }}
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                @if ($item['is_transfer'])
                                    <a href="{{ route('filament.admin.resources.transfers.view-transfer', $item['id'] ?? '') }}"
                                        target="_blank"
                                        class="text-red-600 hover:text-red-800 underline">{{ $item['no_spb'] ?? '-' }}
                                    </a>
                                @else
                                    <a href="{{ route('filament.admin.resources.penjualans.view-penjualan', $item['id'] ?? '') }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline">{{ $item['no_spb'] ?? '-' }}
                                    </a>
                                @endif
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                @if ($item['is_transfer'])
                                    <span>
                                        Transfer Keluar Ke {{ $item['silo_masuk_nama'] }}
                                    </span>
                                @else
                                    <span>
                                        Penjualan
                                    </span>
                                @endif
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                {{ number_format($item['netto'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @php $penjualanIndex++; @endphp
                    @empty
                        <tr>
                            <td colspan="4"
                                class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm text-gray-500">
                                Tidak ada data penjualan atau transfer keluar yang sesuai dengan lumbung
                                "{{ $silo->nama }}"
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="3"
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

            if (document.getElementById('showing-lumbung')) {
                document.getElementById('showing-lumbung').textContent = Math.min(lumbungRows.length, 5);
            }
            if (document.getElementById('showing-penjualan')) {
                document.getElementById('showing-penjualan').textContent = Math.min(penjualanRows.length, 5);
            }
        });
    </script>
</x-filament-panels::page>