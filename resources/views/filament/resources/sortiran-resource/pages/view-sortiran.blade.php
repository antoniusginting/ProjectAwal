<x-filament-panels::page>
    <div class="p-6 bg-white max-w-4xl mx-auto shadow rounded-lg">
        <!-- Responsive Card Using Tailwind CSS -->
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                    <!-- Kolom Kiri -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">No SPB</p>
                            <p class="text-gray-600">: {{ $sortiran->pembelian->no_spb }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Supplier</p>
                            <p class="text-gray-600">: {{ $sortiran->pembelian->supplier->nama_supplier }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Plat Polisi</p>
                            <p class="text-gray-600">
                                <a href="#" class="text-blue-500 hover:underline">
                                    : {{ $sortiran->pembelian->plat_polisi }}
                                </a>
                            </p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Kadar Air</p>
                            <p class="text-gray-600">: {{ $sortiran->kadar_air }}%</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Tanggal</p>
                            <p class="text-gray-600">: {{ $sortiran->created_at }}</p>
                        </div>
                    </div>
                    <!-- Kolom Kanan -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Bruto</p>
                            <p class="text-gray-600">: {{ number_format($sortiran->bruto ?? 19000, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Tara</p>
                            <p class="text-gray-600">: {{ number_format($sortiran->tara ?? 4000, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Netto</p>
                            <p class="text-gray-600">: {{ number_format($sortiran->netto ?? 15000, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Timbangan</p>
                            <p class="text-gray-600">: ke - {{ $sortiran->pembelian->keterangan }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800">Lumbung Basah</p>
                            <p class="text-gray-600">: {{ $sortiran->no_lumbung }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2 mb-2 border-t pt-2">
            <table class="w-full border-collapse border mt-2">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">No</th>
                        <th class="border px-4 py-2">Jenis Kualitas Jagung</th>
                        <th class="border px-4 py-2">Silang Jagung</th>
                        <th class="border px-4 py-2">Jumlah Karung</th>
                        <th class="border px-4 py-2">Tonase</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $total_karung = 0;
                        $total_tonase = 0;
                    @endphp
                    @for ($i = 1; $i <= 6; $i++)
                        @php
                            $jumlah_karung = $sortiran["jumlah_karung_$i"] ?? 0;
                            $tonase =
                                $jumlah_karung > 0
                                    ? floatval(
                                        str_replace(',', '.', str_replace('.', '', $sortiran["tonase_$i"] ?? '0')),
                                    )
                                    : 0;
                        @endphp

                        {{-- Jika jumlah_karung = 0, skip iterasi --}}
                        @if ($jumlah_karung == 0)
                            @continue
                        @endif

                        {{-- Tambahkan ke total --}}
                        @php
                            $total_karung += $jumlah_karung;
                            $total_tonase += $tonase;
                        @endphp
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $i }}</td>
                            <td class="border px-4 py-2">{{ $sortiran["jenis_jagung_$i"] ?? '-' }}</td>
                            <td class="border px-4 py-2">{{ $sortiran["x1_x10_$i"] ?? '-' }}</td>
                            <td class="border px-4 py-2 text-center">{{ $jumlah_karung }}</td>
                            <td class="border px-4 py-2 text-center"> {{ number_format($tonase, 3, ',', '.') }}</td>
                        </tr>
                    @endfor

                    {{-- Baris Total --}}
                    <tr class="bg-gray-200 font-semibold">
                        <td colspan="3" class="border px-4 py-2 text-center">Total</td>
                        <td class="border px-4 py-2 text-center">{{ $total_karung }}</td>
                        <td class="border px-4 py-2 text-center">{{ number_format($total_tonase, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
