<x-layout title="TT Match — Table Tennis Match Preview">

    <div class="space-y-8">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Match Preview</h1>
            <p class="text-gray-500 dark:text-white/50">Select a match to view the full preview</p>
        </div>

        @if($matches->isEmpty())
            <x-empty-state message="No completed matches available yet." />
        @else
            <div class="grid grid-cols-1 gap-3">
                @foreach($matches as $match)
                    <a href="{{ route('matches.preview', $match->id) }}"
                       class="group flex items-center justify-between rounded-2xl bg-gray-50 border border-gray-200 dark:bg-white/[0.03] dark:border-white/[0.06] p-5 transition-all duration-200 hover:border-gray-300 dark:hover:border-white/[0.12] hover:bg-gray-100 dark:hover:bg-white/[0.05]">

                        <div class="flex items-center gap-6">
                            <div class="text-right min-w-[140px]">
                                <p class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $match->playerA->full_name }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40">{{ $match->playerA->country_code }} @if($match->playerA->world_ranking)#{{ $match->playerA->world_ranking }}@endif</p>
                            </div>

                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-bold text-gray-400 dark:text-white/30 uppercase">{{ $match->player_a_sets }} — {{ $match->player_b_sets }}</span>
                                <span class="text-[10px] text-gray-300 dark:text-white/20 font-semibold">VS</span>
                            </div>

                            <div class="min-w-[140px]">
                                <p class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $match->playerB->full_name }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40">{{ $match->playerB->country_code }} @if($match->playerB->world_ranking)#{{ $match->playerB->world_ranking }}@endif</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-500 dark:text-white/60">{{ $match->tournament->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-white/40">{{ $match->match_date->format('M d, Y') }} · {{ $match->round }}</p>
                        </div>

                        <svg class="w-5 h-5 text-gray-300 dark:text-white/20 group-hover:text-gray-500 dark:group-hover:text-white/40 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</x-layout>
