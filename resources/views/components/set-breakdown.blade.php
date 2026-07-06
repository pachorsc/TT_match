@props(['sets', 'playerA', 'playerB'])

@if($sets->isEmpty())
    <div class="max-w-xs mx-auto">
        <x-empty-state message="No set data available." />
    </div>
@else
    <div class="max-w-xs sm:max-w-sm mx-auto card-glass overflow-hidden">
        {{-- Header --}}
        <div class="grid grid-cols-[40px_1fr_48px_1fr] items-center px-4 py-2.5 border-b border-white/[0.06]">
            <span class="text-[10px] font-bold uppercase tracking-[0.12em] text-white/30">Set</span>
            <span class="text-[10px] font-bold uppercase tracking-[0.12em] text-white/30 text-right pr-3">{{ $playerA->first_name }}</span>
            <span class="text-[10px] font-bold uppercase tracking-[0.12em] text-white/30 text-center">vs</span>
            <span class="text-[10px] font-bold uppercase tracking-[0.12em] text-white/30 text-left pl-3">{{ $playerB->first_name }}</span>
        </div>

        {{-- Sets --}}
        @foreach($sets as $set)
            @php
                $aWins = $set->player_a_points > $set->player_b_points;
                $bWins = $set->player_b_points > $set->player_a_points;
            @endphp
            <div class="grid grid-cols-[40px_1fr_48px_1fr] items-center px-4 py-2.5 {{ !$loop->last ? 'border-b border-white/[0.04]' : '' }} transition-colors hover:bg-white/[0.02]">
                <span class="text-xs font-semibold text-white/40">{{ $set->set_number }}</span>
                <span class="text-right pr-3 text-sm font-bold {{ $aWins ? 'text-emerald-400' : 'text-white/70' }}">
                    {{ $set->player_a_points }}
                </span>
                <span class="text-center text-xs font-medium text-white/20">:</span>
                <span class="text-left pl-3 text-sm font-bold {{ $bWins ? 'text-emerald-400' : 'text-white/70' }}">
                    {{ $set->player_b_points }}
                </span>
            </div>
        @endforeach
    </div>
@endif
