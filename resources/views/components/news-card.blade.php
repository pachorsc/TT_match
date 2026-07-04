@props(['news'])

<div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] p-5 space-y-3 hover:bg-white/[0.05] transition-colors">
    <div class="flex items-center justify-between gap-3">
        <span class="text-xs font-semibold text-white/40 uppercase tracking-wider">{{ $news->source }}</span>
        <span class="text-xs text-white/30">{{ $news->published_at->diffForHumans() }}</span>
    </div>

    <h4 class="font-bold text-sm leading-snug line-clamp-2">{{ $news->headline }}</h4>

    <p class="text-xs text-white/50 leading-relaxed line-clamp-3">{{ $news->summary }}</p>

    @if($news->player)
        <div class="pt-2 border-t border-white/[0.06]">
            <span class="text-xs text-white/30">Related: </span>
            <span class="text-xs text-white/60 font-medium">{{ $news->player->full_name }}</span>
        </div>
    @endif
</div>
