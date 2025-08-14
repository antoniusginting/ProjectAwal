<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Header Surat -->
        <div class="text-left space-y-1">
            <h1 class="text-3xl font-bold">Bonar Jaya AdiPerkasa Nusantara</h1>
            <h2 class="text-lg">Surat Timbangan transfer</h2>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>



        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">

            <table class="w-full">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left align-top whitespace-nowrap">Tanggal</td>
                        <td class="whitespace-nowrap">: {{ $transfer->created_at->format('d-m-Y') }}</td>
                        <td class="font-semibold text-left align-top whitespace-nowrap">No IO Keluar</td>
                        <td class="whitespace-nowrap">: <a
                                href="{{ route('filament.admin.resources.laporan-lumbungs.view-laporan-lumbung', $transfer->laporanLumbungKeluar->id ?? '') }}"
                                target="_blank"
                                class="text-blue-600 hover:text-blue-800 underline">{{ $transfer->laporanLumbungKeluar->kode ?? '-' }}
                                -
                                {{ $transfer->laporanLumbungKeluar->lumbung ?? '-' }} </a></td>
                        <td class="font-semibold whitespace-nowrap">Jam Masuk</td>
                        <td class="whitespace-nowrap">: {{ $transfer->jam_masuk }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Operator</td>
                        <td class="whitespace-nowrap">: {{ $transfer->user->name }}</td>
                        <td class="font-semibold whitespace-nowrap">No IO Masuk</td>
                        <td class="whitespace-nowrap">: <a
                                href="{{ route('filament.admin.resources.laporan-lumbungs.view-laporan-lumbung', $transfer->laporanLumbungMasuk->id ?? '') }}"
                                target="_blank"
                                class="text-blue-600 hover:text-blue-800 underline">{{ $transfer->laporanLumbungMasuk->kode ?? '-' }}
                                -
                                {{ $transfer->laporanLumbungMasuk->status_silo ?? ($transfer->laporanLumbungMasuk->lumbung ?? '-') }}</a>
                        </td>
                        <td class="font-semibold whitespace-nowrap">Jam Keluar</td>
                        <td class="whitespace-nowrap">: {{ $transfer->jam_keluar }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <div class="text-right text-sm mb-2">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">No SPB</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Plat Polisi</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Supir</th>
                        {{-- <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Satuan Muatan</th> --}}
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Nama Barang</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm" colspan="2">Berat</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap"
                            rowspan="3">
                            {{ $transfer->kode }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap"
                            rowspan="3">
                            {{ $transfer->plat_polisi }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap"
                            rowspan="3">
                            {{ $transfer->nama_supir }}
                        </td>
                        {{-- <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap"
                            rowspan="3">
                            @if ($transfer->brondolan == 'GONI')
                                @php
                                    $adaGoni = true;
                                @endphp
                                {{ $transfer->jumlah_karung }} - {{ $transfer->brondolan }}
                            @else
                                {{ $transfer->brondolan }}
                            @endif
                        </td> --}}
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 whitespace-nowrap"
                            rowspan="3">
                            {{ $transfer->nama_barang }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 whitespace-nowrap">Bruto</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                            {{ number_format($transfer->bruto, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 whitespace-nowrap">Tara</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                            {{ number_format($transfer->tara, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 whitespace-nowrap">Netto</td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-right whitespace-nowrap">
                            {{ number_format($transfer->netto, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Catatan Langsir --}}
        @if ($transfer->penjualan_id)
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-200">Catatan Langsir</h4>
                </div>
                <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    Transfer ini merupakan <strong>langsir</strong> yang terkait dengan timbangan penjualan
                    @if ($transfer->penjualan)
                        - SPB: <a
                            href="{{ route('filament.admin.resources.penjualans.view-penjualan', $transfer->penjualan->id ?? '') }}"
                            target="_blank" class="underline">{{ $transfer->penjualan->no_spb }}</a>
                    @endif
                    ke <a href="{{ route('filament.admin.resources.silos.view-silo', $transfer->silo->id ?? '') }}"
                        target="_blank" class="underline">{{ $transfer->silo->nama }}</a>
                </p>
            </div>
        @elseif ($transfer->silo_keluar_id)
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200">Catatan Transfer Antar Silo</h4>
                </div>
                <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    Transfer ini merupakan <strong>langsir antar silo</strong> dari
                    <a href="{{ route('filament.admin.resources.silos.view-silo', $transfer->siloKeluar->id ?? '') }}"
                        target="_blank" class="underline">{{ $transfer->siloKeluar->nama }}</a>
                    ke
                    <a href="{{ route('filament.admin.resources.silos.view-silo', $transfer->siloMasuk->id ?? '') }}"
                        target="_blank" class="underline">{{ $transfer->siloMasuk->nama }}</a>
                </p>
            </div>
        @endif

        <!-- Tanda Tangan -->
        {{-- <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">TTD OPERATOR</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-gray-500 dark:text-gray-400">Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 dark:border-gray-700 w-56 mx-auto"></div>
            </div>
        </div> --}}


    </div>
</x-filament-panels::page>
