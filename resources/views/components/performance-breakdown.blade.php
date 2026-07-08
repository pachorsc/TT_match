@props(['stats', 'streak'])

<div class="card-glass p-5 sm:p-6">
    <div class="flex items-center gap-3 mb-5">
        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
        <h3 class="section-heading">Career Performance</h3>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <div class="text-center space-y-1">
            <p class="stat-value text-xl sm:text-2xl">{{ $stats['total_matches'] }}</p>
            <span class="stat-label block">Matches</span>
        </div>
        <div class="text-center space-y-1">
            <p class="text-xl sm:text-2xl font-bold text-emerald-400">{{ $stats['wins'] }}</p>
            <span class="stat-label block">Wins</span>
        </div>
        <div class="text-center space-y-1">
            <p class="text-xl sm:text-2xl font-bold text-red-400">{{ $stats['losses'] }}</p>
            <span class="stat-label block">Losses</span>
        </div>
        <div class="text-center space-y-1">
            <p class="stat-value text-xl sm:text-2xl">{{ $stats['win_rate'] }}%</p>
            <span class="stat-label block">Win %</span>
        </div>
    </div>

    @if($streak['type'] && $streak['count'] > 0)
        <div class="mt-5 pt-5 border-t border-black/[0.06] dark:border-white/[0.06] text-center">
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-xl {{ $streak['type'] === 'W' ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                <span class="text-sm font-semibold text-gray-400 dark:text-white/50 uppercase tracking-wider">Current Streak</span>
                <span class="text-lg font-black {{ $streak['type'] === 'W' ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $streak['type'] === 'W' ? 'W' : 'L' }}{{ $streak['count'] > 1 ? '×' . $streak['count'] : '' }}
                </span>
            </div>
        </div>
    @endif
</div>
