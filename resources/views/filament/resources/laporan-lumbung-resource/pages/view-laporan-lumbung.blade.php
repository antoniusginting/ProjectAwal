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
            // Inisialisasi variabel untuk perhitungan
            $dryers = $laporanlumbung->dryers->values();
            $transferMasuk = $laporanlumbung->transferMasuk->values();
            $penjualanFiltered = $laporanlumbung->penjualans->filter(fn($p) => !empty($p->no_spb));
            $transferKeluar = $laporanlumbung->transferKeluar->values();

            // Gabungkan data masuk (dryers + transferMasuk)
            $dataMasuk = collect();
            foreach ($dryers as $dryer) {
                $dataMasuk->push(
                    (object) [
                        'type' => 'dryer',
                        'data' => $dryer,
                        'created_at' => $dryer->created_at,
                    ],
                );
            }
            foreach ($transferMasuk as $transfer) {
                $dataMasuk->push(
                    (object) [
                        'type' => 'transfer_masuk',
                        'data' => $transfer,
                        'created_at' => $transfer->created_at,
                    ],
                );
            }

            // Gabungkan data keluar (penjualan + transferKeluar)
            $dataKeluar = collect();
            foreach ($penjualanFiltered as $penjualan) {
                $dataKeluar->push(
                    (object) [
                        'type' => 'penjualan',
                        'data' => $penjualan,
                        'created_at' => $penjualan->created_at,
                    ],
                );
            }
            foreach ($transferKeluar as $transfer) {
                $dataKeluar->push(
                    (object) [
                        'type' => 'transfer_keluar',
                        'data' => $transfer,
                        'created_at' => $transfer->created_at,
                    ],
                );
            }

            // Tentukan jumlah baris maksimum untuk tabel
            $maxRows = max($dataMasuk->count(), $dataKeluar->count());

            // Variabel untuk tracking total
            $totalNettoPenjualansBaru = $penjualanFiltered->sum('netto');
            $totalTransferKeluar = $transferKeluar->sum('netto');
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
                @for ($i = 0; $i < $maxRows; $i++)
                    @php
                        // Ambil data untuk baris ini
                        $itemMasuk = $dataMasuk->get($i);
                        $itemKeluar = $dataKeluar->get($i);
                    @endphp

                    <tr>
                        {{-- Kolom Tanggal --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            {{ $itemMasuk?->created_at->format('d-m') ?: ($itemKeluar?->created_at->format('d-m') ?: '') }}
                        </td>

                        {{-- Kolom Jenis --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($itemMasuk && $itemMasuk->type == 'dryer')
                                {{ $itemMasuk->data->nama_barang }}
                            @elseif ($itemMasuk && $itemMasuk->type == 'transfer_masuk')
                                
                            @endif
                        </td>

                        {{-- Kolom Masuk --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($itemMasuk)
                                @if ($itemMasuk->type == 'dryer')
                                    <a href="{{ route('filament.admin.resources.dryers.view-dryer', $itemMasuk->data->id) }}"
                                        target="_blank" class="text-blue-600 hover:text-blue-800">
                                        {{ $itemMasuk->data->no_dryer }}
                                    </a>
                                @elseif ($itemMasuk->type == 'transfer_masuk')
                                    {{ $itemMasuk->data->kode ?? 'Transfer' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        {{-- Kolom Berat Masuk --}}
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            @if ($itemMasuk)
                                @if ($itemMasuk->type == 'dryer')
                                    {{ $itemMasuk->data->total_netto ? number_format($itemMasuk->data->total_netto, 0, ',', '.') : '' }}
                                @elseif ($itemMasuk->type == 'transfer_masuk')
                                    {{ $itemMasuk->data->netto ? number_format($itemMasuk->data->netto, 0, ',', '.') : '' }}
                                @endif
                            @endif
                        </td>

                        {{-- Kolom Keluar --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($itemKeluar)
                                @if ($itemKeluar->type == 'penjualan')
                                    {{ $itemKeluar->data->no_spb }}
                                    @if ($itemKeluar->data->silo)
                                        - {{ $itemKeluar->data->silo }}
                                    @endif
                                @elseif ($itemKeluar->type == 'transfer_keluar')
                                    {{ $itemKeluar->data->kode ?? 'Transfer' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        {{-- Kolom Berat Keluar --}}
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            @if ($itemKeluar)
                                @if ($itemKeluar->type == 'penjualan')
                                    {{ $itemKeluar->data->netto ? number_format($itemKeluar->data->netto, 0, ',', '.') : '-' }}
                                @elseif ($itemKeluar->type == 'transfer_keluar')
                                    {{ $itemKeluar->data->netto ? number_format($itemKeluar->data->netto, 0, ',', '.') : '-' }}
                                @endif
                            @endif
                        </td>

                        {{-- Kolom Penanggung Jawab --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($itemKeluar)
                                @if ($itemKeluar->type == 'penjualan')
                                    {{ $itemKeluar->data->user->name ?? '' }}
                                @elseif ($itemKeluar->type == 'transfer_keluar')
                                    {{ $itemKeluar->data->user->name ?? '' }}
                                @endif
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>

            {{-- Footer dengan Total --}}
            @php
                $totalMasuk = $dryers->sum('total_netto') + $transferMasuk->sum('netto');
                $totalKeluar = $totalNettoPenjualansBaru + $totalTransferKeluar;
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
