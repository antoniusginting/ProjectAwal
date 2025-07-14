<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Header Surat -->
        <div class="text-center space-y-1">
            <h1 class="text-3xl font-bold">Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2 class="text-lg">Laporan Penjualan</h2>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <!-- Menggunakan overflow-x-auto hanya untuk mobile agar table tidak overflow -->
            <table class="w-full">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left align-top whitespace-nowrap">Tanggal</td>
                        <td class="whitespace-nowrap" width='200px'>:
                            {{ optional($timbangantronton->created_at)->format('d-m-Y') ?? '-' }}
                        </td>
                        <td class="font-semibold whitespace-nowrap">Jam</td>
                        <td class="whitespace-nowrap" width='200px'>:
                            {{ optional($timbangantronton->created_at)->format('H:i') ?? '-' }}
                        </td>
                        <td class="font-semibold text-center align-top whitespace-nowrap">No Penjualan</td>
                        <td class="whitespace-nowrap">: {{ $timbangantronton->kode ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">:
                            {{ $timbangantronton->user->name }}
                        </td>
                        <td class="font-semibold text-left align-top whitespace-nowrap">
                            {{ !empty($timbangantronton->penjualan1->plat_polisi) ? 'Plat Polisi' : 'No Container' }}
                        </td>
                        <td class="whitespace-nowrap" colspan="3">:
                            {{ $timbangantronton->penjualan1->plat_polisi ?? ($timbangantronton->penjualanAntarPulau1->no_container ?? '') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            {{-- Tabel untuk Plat Polisi --}}
            @if (!empty($timbangantronton->penjualan1->plat_polisi))
                @php
                    $totalNetto = 0;
                    $totalKarung = 0;
                    $adaGoni = false;
                @endphp

                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No_SPB</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Lumbung</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No Lumbung/IO</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Satuan Muatan</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @for ($i = 1; $i <= 6; $i++)
                            @php $penjualan = $timbangantronton->{'penjualan' . $i} ?? null; @endphp
                            <tr>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $i }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    @if ($penjualan && $penjualan->id)
                                        <a href="{{ route('filament.admin.resources.penjualans.view-penjualan', $penjualan->id) }}"
                                            target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                            {{ $penjualan->no_spb ?? '-' }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $penjualan->nama_barang ?? '' }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $penjualan->nama_lumbung ?? '' }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $penjualan->no_lumbung ?? '' }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    @if ($penjualan && $penjualan->brondolan == 'GONI')
                                        @php
                                            $adaGoni = true;
                                            $totalKarung += $penjualan->jumlah_karung;
                                        @endphp
                                        {{ $penjualan->jumlah_karung }} - {{ $penjualan->brondolan }}
                                    @else
                                        {{ $penjualan->brondolan ?? '' }}
                                    @endif
                                </td>
                                <td
                                    class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                                    {{ $penjualan ? number_format($penjualan->netto, 0, ',', '.') : '' }}
                                </td>
                            </tr>
                            @if ($penjualan)
                                @php $totalNetto += $penjualan->netto; @endphp
                            @endif
                        @endfor

                        <!-- TOTAL -->
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="5" class="border p-2 text-center border-gray-300 dark:border-gray-700">Total
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                @if (isset($adaGoni) && $adaGoni)
                                    {{ number_format($totalKarung, 0, ',', '.') }} - GONI
                                @else
                                @endif
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                {{ ($totalNetto ?? 0) == 0 ? '' : number_format($totalNetto, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif

            {{-- Tabel untuk No Container --}}
            @if (empty($timbangantronton->penjualan1->plat_polisi) && !empty($timbangantronton->penjualanAntarPulau1->no_container))
                @php
                    $totalNetto = 0;
                    $totalKarung = 0;
                    $adaGoni = false;
                @endphp

                <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No_SPB</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Kode Segel</th>
                            <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @for ($i = 1; $i <= 6; $i++)
                            @php $penjualanAntarPulau = $timbangantronton->{'penjualanAntarPulau' . $i} ?? null; @endphp
                            <tr>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $i }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $penjualanAntarPulau->kode ?? '' }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $penjualanAntarPulau->nama_barang ?? '' }}
                                </td>
                                <td
                                    class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                    {{ $penjualanAntarPulau->kode_segel ?? '' }}
                                </td>
                                <td
                                    class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                                    {{ $penjualanAntarPulau ? number_format($penjualanAntarPulau->netto, 0, ',', '.') : '' }}
                                </td>
                            </tr>
                            @if ($penjualanAntarPulau)
                                @php $totalNetto += $penjualanAntarPulau->netto; @endphp
                            @endif
                        @endfor

                        <!-- TOTAL -->
                        <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                            <td colspan="3" class="border p-2 text-center border-gray-300 dark:border-gray-700">Total
                            </td>
                            <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap">
                            </td>
                            <td class="border p-2 text-right border-gray-300 dark:border-gray-700 whitespace-nowrap">
                                {{ ($totalNetto ?? 0) == 0 ? '' : number_format($totalNetto, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">Diterima Oleh</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-gray-500 dark:text-gray-400">Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 dark:border-gray-700 w-56 mx-auto"></div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
