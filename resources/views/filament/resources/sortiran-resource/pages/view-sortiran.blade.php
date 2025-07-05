<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-800 w-full mx-auto shadow rounded-lg">

        <!-- Responsive Card Using Tailwind CSS -->
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                    <!-- Kolom Kiri -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">No SPB</p>
                            <p class="text-gray-600 dark:text-gray-400">: 
                                <a href="{{ route('filament.admin.resources.pembelians.view-pembelian', $sortiran->pembelian->id ?? '') }}"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline">{{ $sortiran->pembelian->no_spb }}</a></p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Supplier</p>
                            <p class="text-gray-600 dark:text-gray-400">:
                                {{ $sortiran->pembelian->supplier->nama_supplier ?? '-' }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Plat Polisi</p>
                            <p class="text-gray-600 dark:text-gray-400">
                                <a href="#" class="text-blue-500 hover:underline">
                                    : {{ $sortiran->pembelian->plat_polisi }}
                                </a>
                            </p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Kadar Air</p>
                            <p class="text-gray-600 dark:text-gray-400">: {{ $sortiran->kadar_air }}%</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Tanggal</p>
                            <p class="text-gray-600 dark:text-gray-400">: {{ $sortiran->created_at->format('d-m-Y') }}
                            </p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Jam</p>
                            <p class="text-gray-600 dark:text-gray-400">: {{ $sortiran->created_at->format('h:i') }}
                            </p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Berat Tungkul</p>
                            <p class="text-gray-600 dark:text-gray-400">: {{ $sortiran->berat_tungkul }}</p>
                        </div>
                    </div>
                    <!-- Kolom Kanan -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Bruto</p>
                            <p class="text-gray-600 dark:text-gray-400">:
                                {{ number_format($sortiran->pembelian->bruto, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Tara</p>
                            <p class="text-gray-600 dark:text-gray-400">:
                                {{ number_format($sortiran->pembelian->tara, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Netto</p>
                            <p class="text-gray-600 dark:text-gray-400">:
                                {{ number_format($sortiran->pembelian->netto, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Timbangan</p>
                            <p class="text-gray-600 dark:text-gray-400">: KE - {{ $sortiran->pembelian->keterangan }}
                            </p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Lumbung</p>
                            <p class="text-gray-600 dark:text-gray-400">:
                                {{ $sortiran->kapasitaslumbungbasah->no_kapasitas_lumbung }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Netto Bersih</p>
                            <p class="text-gray-600 dark:text-gray-400">: {{ $sortiran->netto_bersih }}</p>
                        </div>
                        <div class="flex items-center">
                            <p class="w-32 font-semibold text-gray-800 dark:text-gray-300">Keterangan</p>
                            <p class="text-gray-600 dark:text-gray-400">: {{ $sortiran->keterangan }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-2 mb-2 border-t pt-2">
            <table class="w-full border-collapse border mt-2">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
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
                            <td class="border px-4 py-2">{{ $sortiran["kualitas_jagung_$i"] ?? '-' }}</td>
                            <td class="border px-4 py-2">{{ $sortiran["x1_x10_$i"] ?? '-' }}</td>
                            <td class="border px-4 py-2 text-center">{{ $jumlah_karung }}</td>
                            <td class="border px-4 py-2 text-center">{{ number_format($tonase, 0, ',', '.') }}</td>
                        </tr>
                    @endfor

                    {{-- Baris Total --}}
                    <tr class="bg-gray-200 dark:bg-gray-600 font-semibold">
                        <td colspan="3" class="border px-4 py-2 text-center">Total</td>
                        <td class="border px-4 py-2 text-center">{{ $total_karung }}</td>
                        <td class="border px-4 py-2 text-center">{{ number_format($total_tonase, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="p-6 bg-white dark:bg-gray-800 w-full mx-auto shadow rounded-lg">

        <!-- Header Simple -->
        <div class="bg-white border-b-2 border-blue-500 p-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 text-center">FOTO INFORMASI SORTIRAN</h2>
            {{-- <p class="text-gray-600 text-center mt-1">Rekapitulasi Data Sortiran Jagung</p> --}}
        </div>
        @php
            $fotoList = $sortiran->foto_jagung_1;
        @endphp

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            @for ($i = 0; $i < 6; $i++)
                @if (!empty($fotoList[$i]))
                    <div
                        style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #f9f9f9; padding: 10px; text-align: center;">
                        <img src="{{ asset('storage/' . $fotoList[$i]) }}" alt="Foto Jagung {{ $i + 1 }}"
                            style="width: 100%; height: auto; border-radius: 5px; cursor: pointer;"
                            onclick="openImageModal('{{ asset('storage/' . $fotoList[$i]) }}', 'Foto Jagung {{ $i + 1 }}')">
                        <p style="margin-top: 8px; font-size: 14px;">Foto {{ $i + 1 }}</p>
                    </div>
                @endif
            @endfor
        </div>
    </div>

    <!-- Modal untuk menampilkan gambar yang diperbesar -->
    <div id="imageModal"
        style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; 
    background-color: rgba(0,0,0,0.9); overflow: auto;"
        onclick="closeImageModal()">

        <!-- Kontrol Zoom -->
        <div style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 10002; 
        display: flex; gap: 10px; background: rgba(0,0,0,0.7); padding: 10px; border-radius: 25px;"
            onclick="event.stopPropagation();">
            <button onclick="event.stopPropagation(); zoomOut();"
                style="background: rgba(255,255,255,0.9); border: none; border-radius: 50%; 
            width: 40px; height: 40px; cursor: pointer; font-size: 20px; font-weight: bold; display: flex; align-items: center; justify-content: center;">−</button>
            <span
                style="background: rgba(255,255,255,0.9); border-radius: 15px; padding: 8px 15px; font-size: 14px; 
            display: flex; align-items: center; min-width: 50px; justify-content: center;"
                id="zoomLevel">100%</span>
            <button onclick="event.stopPropagation(); zoomIn();"
                style="background: rgba(255,255,255,0.9); border: none; border-radius: 50%; 
            width: 40px; height: 40px; cursor: pointer; font-size: 20px; font-weight: bold; display: flex; align-items: center; justify-content: center;">+</button>
            <button onclick="event.stopPropagation(); resetZoom();"
                style="background: rgba(255,255,255,0.9); border: none; border-radius: 15px; 
            padding: 8px 15px; cursor: pointer; font-size: 12px; margin-left: 10px;">Reset</button>
        </div>

        <div
            style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 80px 20px 20px; box-sizing: border-box;">
            <div style="text-align: center;">
                <img id="modalImage"
                    style="border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); 
                transition: transform 0.3s ease; max-width: 100%; height: auto; cursor: grab;"
                    ondragstart="return false;" onclick="event.stopPropagation();">
                <p id="modalCaption"
                    style="color: white; text-align: center; margin-top: 15px; font-size: 16px; margin-bottom: 20px;">
                </p>
            </div>
        </div>

        <!-- Tombol close -->
        <span
            style="position: fixed; top: 20px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; 
        cursor: pointer; z-index: 10001; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"
            onclick="event.stopPropagation(); closeImageModal();">&times;</span>
    </div>

    <script>
        let currentZoom = 1;
        let isDragging = false;
        let startX, startY, translateX = 0,
            translateY = 0;

        function openImageModal(imageSrc, caption) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalCaption').textContent = caption;

            // Reset zoom dan posisi
            currentZoom = 1;
            translateX = 0;
            translateY = 0;
            updateImageTransform();
            updateZoomLevel();
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            // Reset zoom dan posisi
            currentZoom = 1;
            translateX = 0;
            translateY = 0;
        }

        function zoomIn() {
            currentZoom = Math.min(currentZoom * 1.3, 4); // Maksimal 4x zoom
            updateImageTransform();
            updateZoomLevel();
            updateCursor();
        }

        function zoomOut() {
            currentZoom = Math.max(currentZoom / 1.3, 0.5); // Minimal 50% zoom
            // Reset posisi jika zoom terlalu kecil
            if (currentZoom <= 1) {
                translateX = 0;
                translateY = 0;
            }
            updateImageTransform();
            updateZoomLevel();
            updateCursor();
        }

        function resetZoom() {
            currentZoom = 1;
            translateX = 0;
            translateY = 0;
            updateImageTransform();
            updateZoomLevel();
            updateCursor();
        }

        function updateImageTransform() {
            const image = document.getElementById('modalImage');
            image.style.transform =
                `scale(${currentZoom}) translate(${translateX / currentZoom}px, ${translateY / currentZoom}px)`;
            image.style.transformOrigin = 'center center';
        }

        function updateZoomLevel() {
            document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
        }

        function updateCursor() {
            const image = document.getElementById('modalImage');
            if (currentZoom > 1) {
                image.style.cursor = isDragging ? 'grabbing' : 'grab';
            } else {
                image.style.cursor = 'default';
            }
        }

        // Mouse events untuk drag
        document.getElementById('modalImage').addEventListener('mousedown', function(e) {
            if (currentZoom > 1) {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                this.style.cursor = 'grabbing';
                e.preventDefault();
            }
        });

        document.addEventListener('mousemove', function(e) {
            if (isDragging && currentZoom > 1) {
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                updateImageTransform();
            }
        });

        document.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                updateCursor();
            }
        });

        // Touch events untuk mobile
        document.getElementById('modalImage').addEventListener('touchstart', function(e) {
            if (currentZoom > 1 && e.touches.length === 1) {
                isDragging = true;
                const touch = e.touches[0];
                startX = touch.clientX - translateX;
                startY = touch.clientY - translateY;
                e.preventDefault();
            }
        });

        document.addEventListener('touchmove', function(e) {
            if (isDragging && currentZoom > 1 && e.touches.length === 1) {
                const touch = e.touches[0];
                translateX = touch.clientX - startX;
                translateY = touch.clientY - startY;
                updateImageTransform();
                e.preventDefault();
            }
        });

        document.addEventListener('touchend', function() {
            if (isDragging) {
                isDragging = false;
                updateCursor();
            }
        });

        // Menutup modal ketika menekan tombol ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        // Event handling untuk menutup modal hanya ketika klik background
        document.getElementById('imageModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeImageModal();
            }
        });

        // Double-click pada gambar untuk reset zoom
        document.getElementById('modalImage').addEventListener('dblclick', function(event) {
            event.stopPropagation();
            resetZoom();
        });
    </script>

</x-filament-panels::page>
