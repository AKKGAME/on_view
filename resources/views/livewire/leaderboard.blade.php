<div class="max-w-4xl mx-auto py-8 px-4 md:px-6 pb-24">
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-5xl font-bold font-gaming tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500 mb-2">
            GLOBAL LEADERBOARD
        </h1>
        <p class="text-slate-400 text-sm">Top players ranked by total experience points (XP).</p>
    </div>

    <!-- User's Rank Card -->
    @auth
        <div class="bg-slate-900/80 p-5 rounded-2xl border border-purple-500/30 mb-8 shadow-xl relative overflow-hidden">
            <div class="absolute inset-0 bg-purple-900/10 opacity-70 blur-xl"></div>
            <div class="flex justify-between items-center relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-yellow-500 flex items-center justify-center font-bold text-lg text-black">
                        #{{ $currentUserRank }}
                    </div>
                    <div>
                        <p class="text-white font-bold text-lg">{{ auth()->user()->name }} (YOU)</p>
                        <p class="text-xs text-slate-400 font-gaming uppercase tracking-widest">{{ auth()->user()->rank }}</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-xl font-bold text-yellow-400 font-gaming">{{ number_format(auth()->user()->xp) }} XP</p>
                    <p class="text-xs text-blue-400 font-gaming">Level {{ floor(auth()->user()->xp / 1000) + 1 }}</p>
                </div>
            </div>
        </div>
    @endauth

    <!-- Ranking Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        
        <!-- Table Header -->
        <div class="grid grid-cols-[10%_auto_25%_15%] text-left font-bold text-xs uppercase tracking-widest text-slate-500 bg-slate-950 p-4 border-b border-slate-800">
            <div>Rank</div>
            <div>Player</div>
            <div class="text-right">XP Total</div>
            <div class="text-center">Level</div>
        </div>

        <!-- Table Rows -->
        <div class="space-y-1">
            @foreach($topUsers as $index => $user)
                @php
                    $rank = $topUsers->firstItem() + $index;
                    $isCurrentUser = auth()->check() && auth()->user()->id === $user->id;
                    $rankColor = $rank === 1 ? 'border-yellow-500 shadow-lg shadow-yellow-500/10' : ($rank === 2 ? 'border-slate-400 shadow-lg shadow-slate-400/10' : ($rank === 3 ? 'border-orange-500/80 shadow-lg shadow-orange-500/10' : 'border-slate-800'));
                @endphp
                <div class="grid grid-cols-[10%_auto_25%_15%] p-4 border-b border-slate-800/50 hover:bg-slate-800 transition cursor-pointer {{ $isCurrentUser ? 'bg-purple-900/30' : '' }}"
                    title="{{ $isCurrentUser ? 'Your Position' : '' }}">
                    
                    <!-- Rank Number -->
                    <div class="font-gaming font-extrabold text-lg" 
                         style="color: {{ $rank === 1 ? '#FBBF24' : ($rank === 2 ? '#94A3B8' : ($rank === 3 ? '#F97316' : '#5C677C')) }}">
                        #{{ $rank }}
                    </div>

                    <!-- Player Info -->
                    <div class="flex items-center gap-3">
                         <div class="h-8 w-8 rounded-full bg-purple-600 flex items-center justify-center text-sm font-bold text-white">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <span class="font-bold text-sm text-slate-200 truncate">{{ $user->name }}</span>
                    </div>

                    <!-- XP Total -->
                    <div class="text-right font-gaming font-bold text-lg text-blue-300">
                        {{ number_format($user->xp) }}
                    </div>

                    <!-- Level -->
                    <div class="text-center">
                        <div class="inline-block px-3 py-1 text-xs rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/30 font-bold">
                             {{ floor($user->xp / 1000) + 1 }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t border-slate-800/60">
             {{ $topUsers->links() }}
        </div>
    </div>
</div>