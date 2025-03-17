<x-filament::page>
    <div class="p-6">
        <h2 class="text-xl font-bold">Detail Sortiran</h2>

        <!-- Informasi Data -->
        <div class="mt-4">
            <p><strong>ID:</strong> {{ $record->id }}</p>
            <p><strong>Nama:</strong> {{ $record->nama }}</p>
        </div>

        <!-- Gambar -->
        <div x-data="{ show: false }" class="mt-4">
            <p class="font-semibold">Foto:</p>

            <!-- Gambar Kecil (Klik untuk Memperbesar) -->
            <img src="{{ asset('storage/' . $record->foto_jagung_1) }}" 
                 class="w-32 h-32 object-cover cursor-pointer rounded-lg shadow"
                 @click="show = true">

            <!-- Modal untuk Gambar Besar -->
            <div x-show="show"
                 class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-start justify-center overflow-auto"
                 x-cloak
                 @keydown.window.escape="show = false"
                 @click="show = false"> <!-- ✅ Tambahkan event ini -->

                <!-- Kontainer untuk gambar, klik di dalam sini tidak akan menutup modal -->
                <div class="relative p-4" @click.stop>
                    <!-- Tombol Close (Selalu di Kanan Atas) -->
                    <button @click="show = false"
                            class="fixed top-4 right-4 bg-red-600 text-white text-3xl font-bold w-10 h-10 flex items-center justify-center rounded-full shadow-lg hover:bg-red-700 z-[9999]">
                        &times;
                    </button>

                    <!-- Gambar Besar Bisa Scroll Jika Melebihi Layar -->
                    <div class="w-full flex justify-center mt-4">
                        <img src="{{ asset('storage/' . $record->foto_jagung_1) }}" 
                             class="max-w-full max-h-screen object-contain rounded-lg shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pastikan Alpine.js tersedia -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-filament::page>
