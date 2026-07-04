@props(['tournament', 'match'])

<div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] px-6 py-5">
    <div class="text-center space-y-2">
        <p class="text-xs font-semibold uppercase tracking-wider text-white/40">{{ $tournament->name }}</p>

        <div class="flex items-center justify-center gap-3 text-sm text-white/50">
            <span>{{ $tournament->location }}, {{ $tournament->country }}</span>
            @if($tournament->category)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/10 text-white/50">
                    {{ $tournament->category }}
                </span>
            @endif
        </div>

        <div class="flex items-center justify-center gap-4 text-sm">
            <span class="text-white/60">{{ $match->match_date->format('d M Y') }}</span>
            @if($match->match_time)
                <span class="text-white/40">{{ $match->match_time }}</span>
            @endif
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/10 text-white/50">
                {{ $match->round }}
            </span>
        </div>
    </div>
</div>
