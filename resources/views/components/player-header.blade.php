@props(['player', 'ranking' => null])

<div class="flex items-center gap-4">
    <span class="text-3xl font-bold tracking-tight">{{ $player->full_name }}</span>

    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-white/10 text-white/70 border border-white/10">
        {{ $player->country_code }}
    </span>

    @if($player->world_ranking)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
            #{{ $player->world_ranking }}
        </span>
    @endif

    @if($player->rating_points)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
            {{ number_format($player->rating_points) }} pts
        </span>
    @endif

    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/5 text-white/50 border border-white/10">
        {{ $player->dominant_hand }}
    </span>
</div>
