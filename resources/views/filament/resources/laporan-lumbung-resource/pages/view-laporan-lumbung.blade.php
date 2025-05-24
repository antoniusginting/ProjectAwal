<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-md shadow-md space-y-6 text-gray-900 dark:text-gray-200">

        <!-- Info Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full align-left">
                <tbody class="text-base">
                    <tr>
                        <td class="font-semibold text-left whitespace-nowrap" width='180px'>Tanggal</td>
                        <td class="whitespace-nowrap" width='200px'>:
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-y') : 'Tanggal kosong' }}
                        <td class="font-semibold whitespace-nowrap" width='250px'>No Laporan</td>
                        <td class="whitespace-nowrap" width='180px'>: {{ $laporanlumbung->kode }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold whitespace-nowrap">Jam</td>
                        <td class="whitespace-nowrap">:
                            {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('h:i') : 'Tanggal kosong' }}
                        </td>
                        <td class="font-semibold whitespace-nowrap">Lumbung</td>
                        <td class="whitespace-nowrap">: {{ $laporanlumbung->dryers->first()->lumbung_tujuan }}</td>

                    </tr>
                </tbody>
            </table>
        </div>


        <!-- Divider -->
        <div class="border-b border-gray-300 dark:border-gray-700"></div>

        {{-- <!-- Tabel Detail Pengiriman -->
        <div class="overflow-x-auto">
            <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
                <div class="text-right text-sm mb-2">Print Date:
                    {{ $laporanlumbung->created_at ? $laporanlumbung->created_at->format('d-m-y') : 'Tanggal kosong' }}
                </div>
                @php
                    // Hitung total berat seluruh timbangan dari semua dryers
                    $totalKeseluruhan = $laporanlumbung->dryers
                        ->flatMap(fn($dryer) => $dryer->timbangantrontons)
                        ->sum('total_netto');
                @endphp
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Masuk</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Keluar</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                        <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">PJ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporanlumbung->dryers as $dryer)
                        @php
                            $count = $dryer->timbangantrontons->count();
                            // Pastikan rowspan minimal 1 agar tidak error kalau count=0
                            $rowspan = $count > 0 ? $count : 1;
                            $totalBerat = $dryer->timbangantrontons->sum('total_netto');
                        @endphp

                        @if ($count > 0)
                            @foreach ($dryer->timbangantrontons as $index => $timbangan)
                                <tr>
                                    @if ($index === 0)
                                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"
                                            rowspan="{{ $rowspan }}">
                                            {{ $dryer->created_at ? $dryer->created_at->format('d-m') : '-' }}
                                        </td>
                                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"
                                            rowspan="{{ $rowspan }}">
                                            {{ $dryer->nama_barang }}
                                        </td>
                                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"
                                            rowspan="{{ $rowspan }}">
                                            {{ $dryer->no_dryer }}
                                        </td>
                                    @endif

                                    <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                        {{ $timbangan->kode }}
                                    </td>
                                    <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                                        {{ number_format($timbangan->total_netto, 0, ',', '.') }}
                                    </td>

                                    @if ($index === 0)
                                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"
                                            rowspan="{{ $rowspan }}">
                                            {{ $laporanlumbung->user->name }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            {{-- Tambahkan baris total berat --}}
        {{-- <tr>
                                <td colspan="3"></td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm font-semibold"
                                    colspan="1">
                                    <strong>Total Berat</strong>
                                </td>
                                <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm font-semibold"
                                    colspan="1">
                                    <strong>{{ number_format($totalBerat, 0, ',', '.') }}</strong>
                                </td>
                                {{-- Kolom lain kosong --}}
        {{--  <td class="border p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                            </tr>
                        @else
                            {{-- Jika tidak ada timbangan tronton, tampilkan satu baris kosong --}}
        {{-- <tr>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $dryer->created_at ? $dryer->created_at->format('d-m') : '-' }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $dryer->nama_barang }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $dryer->no_dryer }}
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm"
                                    colspan="2" style="text-align: center;">
                                    Tidak ada data timbangan tronton
                                </td>
                                <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                                    {{ $laporanlumbung->pj }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                        <td colspan="4" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            Total Keseluruhan Berat:</td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                        </td>
                        <td class="border p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                    </tr>
                </tfoot>


            </table>
        </div> --}}

        {{-- <!-- Tanda Tangan -->
        <div class="flex justify-end mt-10">
            <div class="text-center">
                <p class="text-lg font-semibold">TTD OPERATOR</p>
                <div class="mt-4 h-24 w-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-md">
                    <span class="text-gray-500 dark:text-gray-400">Tanda Tangan</span>
                </div>
                <div class="mt-4 border-b border-gray-300 dark:border-gray-700 w-56 mx-auto"></div>
            </div>
        </div> --}}

        @php
            $dryers = $laporanlumbung->dryers->sortBy('created_at')->values();
            $timbangan = $laporanlumbung->timbangantrontons->sortBy('created_at')->values();
            $max = max($dryers->count(), $timbangan->count());
            $totalKeseluruhan = $laporanlumbung->timbangantrontons->sum('total_netto');
        @endphp

        <table class="w-full border border-collapse border-gray-300 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">TGL</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Jenis</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Masuk</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Keluar</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">Berat</th>
                    <th class="border p-2 border-gray-300 dark:border-gray-700 text-sm">PJ</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $max; $i++)
                    @php
                        $dryer = $dryers->get($i);
                        $timbanganItem = $timbangan->get($i);
                    @endphp
                    <tr>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">{{ $dryer ? $dryer->created_at->format('d-m') : '' }}</td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">{{ $dryer ? $dryer->nama_barang : '' }}</td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">{{ $dryer ? $dryer->no_dryer : '' }}</td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">{{ $timbanganItem ? $timbanganItem->kode : '-' }}</td>
                        <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                            {{ $timbanganItem ? number_format($timbanganItem->total_netto, 0, ',', '.') : '-' }}
                        </td>
                        <td class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                            @if ($i == 0)
                                {{ $laporanlumbung->user->name }}
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 font-semibold">
                    <td colspan="4" class="border p-2 text-center border-gray-300 dark:border-gray-700 text-sm">
                        Total Berat:</td>
                    <td class="border p-2 text-right border-gray-300 dark:border-gray-700 text-sm">
                        {{ number_format($totalKeseluruhan, 0, ',', '.') }}
                    </td>
                    <td class="border p-2 border-gray-300 dark:border-gray-700 text-sm"></td>
                </tr>
            </tfoot>
        </table>

    </div>
</x-filament-panels::page>
