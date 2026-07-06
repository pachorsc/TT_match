<x-layout title="TT Match — Table Tennis Match Preview">

    <div class="space-y-10 sm:space-y-12">

        {{-- Hero Section --}}
        <div class="text-center space-y-5 sm:space-y-6 py-4 sm:py-6">
            <div class="space-y-3">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.1]">
                    Table Tennis<br>
                    <span class="text-sport-400">Match Preview</span>
                </h1>
                <p class="text-sm sm:text-base text-white/40 max-w-lg mx-auto leading-relaxed">
                    Explora partidos, compara jugadores cara a cara y sigue
                    las estadísticas de los mejores del mundo.
                </p>
            </div>

            {{-- Stats Bar --}}
            @if($totalMatches > 0 || $totalTournaments > 0 || $totalPlayers > 0)
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-white/[0.03] border border-white/[0.06]">
                    <span class="text-sm sm:text-base font-bold text-white/80">{{ $totalMatches }}</span>
                    <span class="text-xs text-white/30">partidos</span>
                    <span class="w-1 h-1 rounded-full bg-white/10 mx-1"></span>
                    <span class="text-sm sm:text-base font-bold text-white/80">{{ $totalTournaments }}</span>
                    <span class="text-xs text-white/30">torneos</span>
                    <span class="w-1 h-1 rounded-full bg-white/10 mx-1"></span>
                    <span class="text-sm sm:text-base font-bold text-white/80">{{ $totalPlayers }}</span>
                    <span class="text-xs text-white/30">jugadores</span>
                </div>
            @else
                <p class="text-sm text-white/30">No data available yet. Import match data to get started.</p>
            @endif

            {{-- CTA --}}
            <div class="pt-1">
                <a href="{{ route('compare') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-sport-400 bg-sport-500/15 border border-sport-500/25 hover:bg-sport-500/25 hover:border-sport-500/40 transition-all duration-200">
                    Comparar jugadores
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

    </div>

</x-layout>
