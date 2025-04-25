<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Header Surat -->
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-bold">{{ $suratjalan->kontrak2->nama }}</h1>
            <h2 class="text-lg">Surat Jalan Pengiriman</h2>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        <!-- Info Kota dan Tanggal -->
        <div class="flex justify-between">
            <div>
                <span class="block">{{ $suratjalan->kota }}, {{ $suratjalan->created_at->format('d-m-Y') }}</span>
            </div>
            <div>
                <span class="block"></span>
            </div>
        </div>

        <!-- Info Pengiriman -->
        <div>
            <table class="w-full">
                <tbody>
                    <tr>
                        <td class="font-semibold text-left align-top">Kepada Yth.</td>
                        <td class="text-left align-top">: {{ $suratjalan->kontrak->nama }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-left align-top">Alamat</td>
                        <td class="text-left align-top">: {{ $suratjalan->alamat->alamat }}</td>
                    </tr>
                    @if (!empty($suratjalan->po))
                        <tr>
                            <td class="font-semibold text-left align-top">No PO</td>
                            <td class="text-left align-top">: {{ $suratjalan->po }}</td>
                        </tr>
                    @endif

                </tbody>
            </table>
        </div>

        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>
        <div class="text-right text-sm">Print Date : {{ now()->format('d-m-Y H:i:s') }}</div>
        <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700">
                            @if (!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                Plat Polisi
                            @else
                                No Container
                            @endif
                        </th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Nama Supir</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Satuan Muatan</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700">Nama Barang</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700" colspan="2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            @if (!empty($suratjalan->tronton->penjualan1->plat_polisi))
                                {{ $suratjalan->tronton->penjualan1->plat_polisi }}
                            @else
                                {{ $suratjalan->tronton->penjualan1->no_container }}
                            @endif
                            - {{ $suratjalan->jenis_mobil }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            {{ $suratjalan->tronton->penjualan1->nama_supir }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            @php
                                $totalKarung = 0;
                                for ($i = 1; $i <= 6; $i++) {
                                    $penjualan = $suratjalan->tronton->{'penjualan' . $i} ?? null;
                                    if ($penjualan && $penjualan->brondolan == 'GONI') {
                                        $totalKarung += $penjualan->jumlah_karung;
                                    }
                                }
                            @endphp
                            @if ($totalKarung > 0)
                                {{ number_format($totalKarung, 0, ',', '.') }} -
                            @endif
                            {{ $suratjalan->tronton->penjualan1->brondolan }}
                        </td>
                        
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700" rowspan="3">
                            JAGUNG KERING SUPER
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">Bruto</td>
                        <td class="border text-right p-2 border-gray-300 dark:border-gray-700">
                            {{ number_format($suratjalan->bruto_final, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">Tara</td>
                        <td class="border text-right p-2 border-gray-300 dark:border-gray-700">
                            {{ number_format($suratjalan->tronton->tara_awal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border p-2 border-gray-300 dark:border-gray-700">Netto</td>
                        <td class="border text-right p-2 border-gray-300 dark:border-gray-700">
                            {{ number_format($suratjalan->netto_final, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
            
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
