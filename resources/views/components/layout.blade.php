<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? config('app.name', 'TT Match') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

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
<body class="bg-[#070707] text-white min-h-screen font-sans antialiased selection:bg-sport-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <header class="flex items-center justify-between mb-8 sm:mb-12 pb-4 border-b border-white/[0.06]">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-sport-500/20 border border-sport-500/30 text-sport-400 font-bold text-sm transition-all duration-300 group-hover:bg-sport-500/30 group-hover:border-sport-500/50">TT</span>
                <span class="text-base font-bold tracking-tight text-white/90 group-hover:text-white transition-colors duration-200">Match</span>
            </a>

            @php
                $currentRoute = request()->route() ? request()->route()->getName() : '';
            @endphp

            <nav class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('home') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ $currentRoute === 'home' ? 'bg-white/10 text-white' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.04]' }}">
                    Partidos
                </a>
                <a href="{{ route('predictions') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ $currentRoute === 'predictions' ? 'bg-white/10 text-white' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.04]' }}">
                    Predicción
                </a>
                <div class="ml-2 pl-2 border-l border-white/[0.06]">
                    <x-theme-toggle />
                </div>
            </nav>
        </header>
        {{ $slot }}
    </div>
</body>
</html>
