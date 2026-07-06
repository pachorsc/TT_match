<x-layout title="404 — Not Found">

    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center space-y-6">
            <div class="space-y-2">
                <h1 class="text-6xl sm:text-7xl font-extrabold tracking-tight text-white/10">404</h1>
                <p class="text-lg font-semibold text-white/60">{{ $message ?? 'Resource not found.' }}</p>
            </div>
            <p class="text-sm text-white/30 max-w-sm mx-auto">The page you are looking for does not exist or has been moved.</p>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-sport-400 bg-sport-500/15 border border-sport-500/25 hover:bg-sport-500/25 hover:border-sport-500/40 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Home
            </a>
        </div>
    </div>

</x-layout>
