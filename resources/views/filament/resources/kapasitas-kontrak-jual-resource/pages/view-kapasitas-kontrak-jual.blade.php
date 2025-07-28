<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">
        @php
            // Data penjualan & surat jalan
            $penjualanFiltered = $kontrakLuar->penjualanLuar ?? collect();
            $suratJalanFiltered = $kontrakLuar->suratJalan ?? collect();
            // Hitung total penerimaan untuk status "terima" dan "setengah"
            $totalBeratPenjualanFiltered = $penjualanFiltered
                ->filter(fn($item) => in_array(strtolower($item->status), ['terima', 'setengah']))
                ->sum('netto_diterima');
            $totalBeratSuratJalanFiltered = $suratJalanFiltered
                ->filter(fn($item) => in_array(strtolower($item->status), ['terima', 'setengah']))
                ->sum('netto_diterima');
            // Total retur dari netto (hanya status "retur") - untuk informasi saja
            $totalReturPenjualan = $penjualanFiltered
                ->filter(fn($item) => strtolower($item->status) === 'retur')
                ->sum('netto');
            $totalReturSuratJalan = $suratJalanFiltered
                ->filter(fn($item) => strtolower($item->status) === 'retur')
                ->sum('netto_final');
            // Total penerimaan
            $totalBeratKeseluruhan = $totalBeratPenjualanFiltered + $totalBeratSuratJalanFiltered;
            // Data summary
            $totalStokDanBerat = $kontrakLuar->stok;
            $stokSisa = $totalStokDanBerat - $totalBeratKeseluruhan;
            $persenanPenjualan = $totalStokDanBerat != 0 ? ($totalBeratKeseluruhan / $totalStokDanBerat) * 100 : 0;
            // Total data
            $laporanPenjualanTotal = $penjualanFiltered->count();
            $laporanSuratJalanTotal = $suratJalanFiltered->count();
        @endphp
        {{-- Summary Dashboard --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg mb-6 shadow-md border">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Ringkasan Tonase Kontrak {{ $kontrakLuar->nama }}
                </h3>
            </div>
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                    Data Tonase Kontrak & Penjualan
                </h4>
                <div class="flex flex-row gap-4">
                    <!-- Stok Awal -->
                    <div class="flex-1 text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Tonase Kontrak</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($kontrakLuar->stok, 0, ',', '.') }}
                        </p>
                    </div>
                    <!-- Penerimaan -->
                    <div class="flex-1 text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Penerimaan</p>
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ number_format($totalBeratKeseluruhan, 0, ',', '.') }}
                        </p>
                    </div>
                    <!-- Retur (Informasi saja - tidak mempengaruhi sisa kontrak) -->
                    <div class="flex-1 text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Retur</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">
                            {{ number_format($totalReturPenjualan + $totalReturSuratJalan, 0, ',', '.') }}
                        </p>
                    </div>
                    <!-- Sisa Stok -->
                    <div
                        class="flex-1 text-center p-3 {{ $stokSisa >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Sisa Kontrak</p>
                        <p
                            class="text-xl font-bold {{ $stokSisa >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($stokSisa, 0, ',', '.') }}
                        </p>
                    </div>
                    <!-- Harga -->
                    <div class="flex-1 text-center p-3 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Harga</p>
                        <p class="text-xl font-bold">
                            {{ number_format($kontrakLuar->harga, 0, ',', '.') }}
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
        {{-- Pesan jika tidak ada data --}}
        @if ($laporanPenjualanTotal == 0 && $laporanSuratJalanTotal == 0)
            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-lg text-center">
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    Tidak ada data penjualan langsung maupun surat jalan untuk kontrak "{{ $kontrakLuar->nama }}"
                </p>
            </div>
        @endif
        {{-- Tabel Penjualan --}}
        @if ($laporanPenjualanTotal > 0)
            <div class="border-b border-gray-300 dark:border-gray-700"></div>
            <div class="mb-6" id="laporan-penjualan">
                <h3 class="text-lg font-semibold mb-3">Laporan Penjualan Langsung</h3>
                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 text-sm">Tanggal</th>
                            <th class="border p-2 text-sm">Kode</th>
                            <th class="border p-2 text-sm">Kode Segel</th>
                            <th class="border p-2 text-sm">Nama Barang</th>
                            <th class="border p-2 text-sm">No Container</th>
                            <th class="border p-2 text-sm">Status</th>
                            <th class="border p-2 text-sm">Netto</th>
                            <th class="border p-2 text-sm">Netto Diterima</th>
                        </tr>
                    </thead>
                    <tbody id="penjualan-tbody">
                        @php $penjualanIndex = 0; @endphp
                        @foreach ($penjualanFiltered as $penjualan)
                            @php
                                // Status yang dihitung: 'terima' dan 'setengah'
                                // Status yang dikosongkan: 'tolak' dan 'retur'
                                $nettoDiterima = in_array(strtolower($penjualan->status), ['terima', 'setengah'])
                                    ? $penjualan->netto_diterima
                                    : 0;
                            @endphp
                            <tr class="penjualan-row {{ $penjualanIndex >= 5 ? 'hidden' : '' }}"
                                data-index="{{ $penjualanIndex }}">
                                <td class="border p-2 text-center text-sm">
                                    {{ \Carbon\Carbon::parse($penjualan->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="border p-2 text-center text-sm">{{ $penjualan->kode ?? '-' }}</td>
                                <td class="border p-2 text-center text-sm">{{ $penjualan->kode_segel ?? '-' }}</td>
                                <td class="border p-2 text-center text-sm">{{ $penjualan->nama_barang ?? '-' }}</td>
                                <td class="border p-2 text-center text-sm">{{ $penjualan->pembelianAntarPulau->no_container ?? '-' }}</td>
                                <td class="border p-2 text-center text-sm">{{ $penjualan->status ?? '-' }}</td>
                                <td class="border p-2 text-right text-sm">
                                    {{ number_format($penjualan->netto ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="border p-2 text-right text-sm">
                                    {{ number_format($nettoDiterima ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @php $penjualanIndex++; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="7" class="border p-2 text-center text-sm">
                                Total Berat Penjualan Langsung
                            </td>
                            <td class="border p-2 text-right text-sm">
                                {{ number_format($totalBeratPenjualanFiltered, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
        {{-- Tabel Surat Jalan --}}
        @if ($laporanSuratJalanTotal > 0)
            <div class="border-b border-gray-300 dark:border-gray-700"></div>
            <div class="mb-6" id="laporan-suratjalan">
                <h3 class="text-lg font-semibold mb-3">Laporan Surat Jalan</h3>
                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 text-sm">No Penjualan</th>
                            <th class="border p-2 text-sm">No PO</th>
                            <th class="border p-2 text-sm">Tanggal</th>
                            <th class="border p-2 text-sm">Status</th>
                            <th class="border p-2 text-sm">Netto</th>
                            <th class="border p-2 text-sm">Netto Diterima</th>
                        </tr>
                    </thead>
                    <tbody id="suratjalan-tbody">
                        @php $suratJalanIndex = 0; @endphp
                        @foreach ($suratJalanFiltered as $suratJalan)
                            @php
                                // Status yang dihitung: 'terima' dan 'setengah'
                                // Status yang dikosongkan: 'tolak' dan 'retur'
                                $nettoDiterima = in_array(strtolower($suratJalan->status), ['terima', 'setengah'])
                                    ? $suratJalan->netto_diterima
                                    : 0;
                            @endphp
                            <tr class="suratjalan-row {{ $suratJalanIndex >= 5 ? 'hidden' : '' }}"
                                data-index="{{ $suratJalanIndex }}">
                                <td class="border p-2 text-center text-sm">
                                    <a href="{{ route('filament.admin.resources.timbangan-trontons.view-laporan-penjualan', $suratJalan->tronton->id ?? '') }}"
                                        target="_blank" class="text-blue-600 underline hover:text-blue-800">
                                        {{ $suratJalan->tronton->kode ?? '-' }}
                                    </a>
                                </td>
                                <td class="border p-2 text-center text-sm">{{ $kontrakLuar->no_po ?? '-' }}</td>
                                <td class="border p-2 text-center text-sm">
                                    {{ \Carbon\Carbon::parse($suratJalan->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="border p-2 text-center text-sm">{{ $suratJalan->status ?? '-' }}</td>
                                <td class="border p-2 text-right text-sm">
                                    {{ number_format($suratJalan->netto_final ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="border p-2 text-right text-sm">
                                    {{ number_format($nettoDiterima ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @php $suratJalanIndex++; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="5" class="border p-2 text-center text-sm">
                                Total Berat Surat Jalan
                            </td>
                            <td class="border p-2 text-right text-sm">
                                {{ number_format($totalBeratSuratJalanFiltered, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
    {{-- JavaScript --}}
    <script>
        function changePenjualanPerPage() {
            const select = document.getElementById('penjualan-per-page');
            const rows = document.querySelectorAll('.penjualan-row');
            let limit = select.value === 'all' ? rows.length : parseInt(select.value);
            rows.forEach((row, i) => row.classList.toggle('hidden', i >= limit));
        }

        function changeSuratJalanPerPage() {
            const select = document.getElementById('suratjalan-per-page');
            const rows = document.querySelectorAll('.suratjalan-row');
            let limit = select.value === 'all' ? rows.length : parseInt(select.value);
            rows.forEach((row, i) => row.classList.toggle('hidden', i >= limit));
        }
    </script>
</x-filament-panels::page>
