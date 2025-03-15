<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Filament Styles -->
    @filamentStyles

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased">

    {{ $slot }}

    <!-- Lightbox Modal -->
    <div x-data="{ open: false, img: '' }"
        x-show="open"
        @lightbox-open.window="img = $event.detail; open = true"
        @keydown.escape.window="open = false"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-80 z-50"
        x-cloak>
        <img :src="img" class="max-w-4xl max-h-screen rounded-lg shadow-lg">
    </div>

    <script>
        document.addEventListener("alpine:init", () => {
            Alpine.store("lightbox", {
                open(img) {
                    window.dispatchEvent(new CustomEvent("lightbox-open", { detail: img }));
                }
            });
        });
    </script>

    @filamentScripts
</body>
</html>
