<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? config('app.name', 'TT Match') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            if (saved) {
                document.documentElement.classList.toggle('dark', saved === 'dark');
            }
        })();
    </script>
</head>
<body class="bg-white dark:bg-[#0a0a0a] text-gray-900 dark:text-white min-h-screen font-sans antialiased transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6 py-10">
        <header class="flex items-center justify-end mb-8">
            <x-theme-toggle />
        </header>
        {{ $slot }}
    </div>
</body>
</html>
