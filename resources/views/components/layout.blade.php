<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? config('app.name', 'TT Match') }}</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            if (saved) {
                document.documentElement.classList.toggle('dark', saved === 'dark');
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="bg-[#f4f5f7] dark:bg-[#070707] text-gray-900 dark:text-white min-h-screen font-sans antialiased selection:bg-sport-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <header class="flex items-center justify-between mb-8 sm:mb-12 pb-4 border-b border-gray-200/80 dark:border-white/[0.06]">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group logo-glow">
                <svg viewBox="0 0 180 40" class="h-9 w-auto transition-all duration-300">
                    <defs>
                        <linearGradient id="sportGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#60a5fa"/>
                            <stop offset="100%" stop-color="#2563eb"/>
                        </linearGradient>
                    </defs>
                    <text x="0" y="32" font-family="Instrument Sans, sans-serif" font-weight="800"
                          font-size="34" fill="url(#sportGrad)">TT</text>
                    <line x1="72" y1="10" x2="72" y2="30" stroke="#d1d5db" class="dark:stroke-white/15" stroke-width="1.5"/>
                    <text x="82" y="32" font-family="Instrument Sans, sans-serif" font-weight="500"
                          font-size="24" fill="#374151" class="dark:fill-white/85">Match</text>
                </svg>
            </a>

            @php
                $currentRoute = request()->route() ? request()->route()->getName() : '';
            @endphp

            <nav class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('home') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ $currentRoute === 'home' ? 'bg-gray-200/70 dark:bg-white/10 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 hover:bg-gray-100/70 dark:hover:bg-white/[0.04]' }}">
                    Inicio
                </a>
                <a href="{{ route('players.index') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ str_starts_with($currentRoute, 'players') ? 'bg-gray-200/70 dark:bg-white/10 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 hover:bg-gray-100/70 dark:hover:bg-white/[0.04]' }}">
                    Jugadores
                </a>
                <a href="{{ route('rankings') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ $currentRoute === 'rankings' ? 'bg-gray-200/70 dark:bg-white/10 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 hover:bg-gray-100/70 dark:hover:bg-white/[0.04]' }}">
                    Rankings
                </a>
                <a href="{{ route('videos') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ $currentRoute === 'videos' ? 'bg-gray-200/70 dark:bg-white/10 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 hover:bg-gray-100/70 dark:hover:bg-white/[0.04]' }}">
                    Videos
                </a>
                <a href="{{ route('compare') }}"
                   class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                   {{ $currentRoute === 'compare' ? 'bg-gray-200/70 dark:bg-white/10 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 hover:bg-gray-100/70 dark:hover:bg-white/[0.04]' }}">
                    Head to Head
                </a>
                <div class="ml-2 pl-2 border-l border-gray-200/80 dark:border-white/[0.06]">
                    <x-theme-toggle />
                </div>
            </nav>
        </header>
        {{ $slot }}
    </div>
</body>
</html>
