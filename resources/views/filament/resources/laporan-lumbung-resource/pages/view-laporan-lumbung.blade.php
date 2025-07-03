<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        {{-- Header Info Laporan --}}
        <div class="overflow-x-auto">
            <table class="w-full align-left">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left whitespace-nowrap" width='180px'>Tanggal</td>
                        <td class="whitespace-nowrap" width='200px'>:
                            {{ $laporanlumbung->created_at?->format('d-m-y') ?? 'Tanggal kosong' }}
                        </td>
                        <td class="font-semibold whitespace-nowrap" width='250px'>No Laporan</td>
                        <td class="whitespace-nowrap" width='180px'>: {{ $laporanlumbung->kode }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Jam</td>
                        <td class="whitespace-nowrap">:
                            {{ $laporanlumbung->created_at?->format('H:i') ?? 'Jam kosong' }}
                        </td>
                        <td class="font-semibold whitespace-nowrap">Lumbung</td>
                        <td class="whitespace-nowrap">:
                            {{ $laporanlumbung->lumbung ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Divider --}}
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        @php
            /**
             * Inisialisasi variabel untuk perhitungan
             */
            $lumbungTujuan = $laporanlumbung->lumbung;
            $dryers = $laporanlumbung->dryers->values();
            $timbangan = $laporanlumbung->timbangantrontons->values();
            $penjualanFiltered = $laporanlumbung->penjualans->filter(fn($p) => !empty($p->no_spb));
            
            // Tentukan jumlah baris maksimum untuk tabel
            $maxRows = max($dryers->count(), $timbangan->count(), $penjualanFiltered->count());
            
            // Variabel untuk tracking total
            $totalKeseluruhanFiltered = 0;
            $totalNettoPenjualansBaru = $penjualanFiltered->sum('netto');
        @endphp

        {{-- Tabel Aktivitas Lumbung --}}
        <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Masuk</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Keluar</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">PJ</th>
                </tr>
            </thead>
            <tbody>
                @php $penjualanIndex = 0; @endphp

                @for ($i = 0; $i < $maxRows; $i++)
                    @php
                        // Ambil data untuk baris ini
                        $dryer = $dryers->get($i);
                        $timbanganItem = $timbangan->get($i);
                        $penjualanItem = $penjualanFiltered->get($penjualanIndex);
                        
                        // Hitung total netto dari timbangan untuk lumbung tujuan
                        $totalNettoTimbangan = 0;
                        if ($timbanganItem && $lumbungTujuan) {
                            $filteredPenjualan = $this->getFilteredPenjualanFromTimbangan($timbanganItem, $lumbungTujuan);
                            $totalNettoTimbangan = $filteredPenjualan->sum('netto');
                            $totalKeseluruhanFiltered += $totalNettoTimbangan;
                        }
                        
                        // Update index penjualan jika ada item
                        if ($penjualanItem) {
                            $penjualanIndex++;
                        }
                    @endphp

                    <tr>
                        {{-- Kolom Tanggal --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $dryer?->created_at->format('d-m') ?: ($penjualanItem?->created_at->format('d-m') ?: '') }}
                        </td>

                        {{-- Kolom Jenis --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $dryer?->nama_barang ?: ($penjualanItem ? '' : '') }}
                        </td>

                        {{-- Kolom Masuk (No Dryer) --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($dryer)
                                <a href="{{ route('filament.admin.resources.dryers.view-dryer', $dryer->id) }}" 
                                   target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $dryer->no_dryer }}
                                </a>
                            @else
                                
                            @endif
                        </td>

                        {{-- Kolom Berat Masuk --}}
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ $dryer?->total_netto ? number_format($dryer->total_netto, 0, ',', '.') : '' }}
                        </td>

                        {{-- Kolom Keluar (Kode/SPB) --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($timbanganItem)
                                {{ $timbanganItem->kode }}
                            @elseif ($penjualanItem)
                                {{ $penjualanItem->no_spb }}
                                @if ($penjualanItem->silo)
                                    - {{ $penjualanItem->silo }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        {{-- Kolom Berat Keluar --}}
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            @if ($timbanganItem)
                                {{ $totalNettoTimbangan > 0 ? number_format($totalNettoTimbangan, 0, ',', '.') : '-' }}
                            @elseif ($penjualanItem)
                                {{ $penjualanItem->netto ? number_format($penjualanItem->netto, 0, ',', '.') : '-' }}
                            @endif
                        </td>

                        {{-- Kolom Penanggung Jawab --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $timbanganItem?->user->name ?: ($penjualanItem?->user->name ?: '') }}
                        </td>
                    </tr>
                @endfor

                {{-- Tampilkan sisa penjualan jika ada --}}
                @for ($j = $penjualanIndex; $j < $penjualanFiltered->count(); $j++)
                    @php $penjualanItem = $penjualanFiltered->get($j); @endphp
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{-- {{ $penjualanItem->created_at->format('d-m') }} --}}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">-</td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">-</td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $penjualanItem->no_spb }}
                            @if ($penjualanItem->silo)
                                - {{ $penjualanItem->silo }}
                            @endif
                        </td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ $penjualanItem->netto ? number_format($penjualanItem->netto, 0, ',', '.') : '-' }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $penjualanItem->user->name ?? '-' }}
                        </td>
                    </tr>
                @endfor
            </tbody>

            {{-- Footer dengan Total --}}
            @php
                $totalMasuk = $dryers->sum('total_netto');
                $totalKeluar = $totalKeseluruhanFiltered + $totalNettoPenjualansBaru;
                $persentaseKeluar = $totalMasuk > 0 ? ($totalKeluar / $totalMasuk) * 100 : 0;
            @endphp

            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                    <td colspan="3" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                        Total:
                    </td>
                    <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                        {{ number_format($totalMasuk, 0, ',', '.') }}
                    </td>
                    <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"></td>
                    <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                        {{ number_format($totalKeluar, 0, ',', '.') }}
                    </td>
                    <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                        @if ($laporanlumbung->lumbung && $laporanlumbung->status)
                            {{ number_format($persentaseKeluar, 2) }}%
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament-panels::page>