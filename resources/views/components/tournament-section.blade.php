@props(['tournament', 'matches', 'startIndex' => 0])

<div class="card-glass overflow-hidden">
    {{-- Tournament Header --}}
    <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-white/[0.06]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-1 h-8 rounded-full bg-sport-500/60 shrink-0"></div>
                <div class="min-w-0">
                    <h2 class="font-bold text-white/90 truncate">{{ $tournament->name }}</h2>
                    <p class="text-xs text-white/40 mt-0.5">
                        {{ $tournament->location }}, {{ $tournament->country_code }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="text-xs text-white/30">
                    {{ $tournament->start_date->format('M d') }}–{{ $tournament->end_date->format('d, Y') }}
                </span>
                @if($tournament->category)
                    <span class="badge-glass text-[10px]">{{ $tournament->category }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Matches --}}
    @if($matches->isEmpty())
        <div class="px-6 py-8 text-center text-sm text-white/30">
            No matches found for this tournament.
        </div>
    @else
        <div class="divide-y divide-white/[0.04]">
            @foreach($matches as $i => $match)
                <x-match-row :match="$match" :index="$startIndex + $i" />
            @endforeach
        </div>
    @endif
</div>
