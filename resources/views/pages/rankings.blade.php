<x-layout title="Rankings">

    <div class="space-y-10 sm:space-y-12">

        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Rankings Mundiales</h1>
            <p class="text-sm sm:text-base text-white/40">Clasificación actualizada de los mejores jugadores</p>
        </div>

        {{-- Gender Tabs --}}
        <div class="flex items-center justify-center gap-2">
            <a href="{{ route('rankings', ['gender' => 'M']) }}"
               class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
               {{ $gender === 'M' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}">
                Masculino
            </a>
            <a href="{{ route('rankings', ['gender' => 'F']) }}"
               class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
               {{ $gender === 'F' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}">
                Femenino
            </a>
        </div>

        {{-- Rankings Table --}}
        <x-rankings-table :rankings="$rankings" />

    </div>

</x-layout>
