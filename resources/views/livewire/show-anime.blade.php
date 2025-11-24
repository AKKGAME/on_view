<div class="min-h-screen pb-20 relative bg-[#05050A] text-white font-sans selection:bg-purple-500 selection:text-white">

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- 1. AMBIENT BACKGROUND --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        @php
            $bgImage = $anime->cover_url 
                ? \Illuminate\Support\Facades\Storage::url($anime->cover_url) 
                : ($anime->thumbnail_url ? \Illuminate\Support\Facades\Storage::url($anime->thumbnail_url) : null);
        @endphp

        @if($bgImage)
            <div class="absolute inset-0">
                <img src="{{ $bgImage }}" class="w-full h-full object-cover opacity-[0.15] blur-[100px] scale-110">
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#05050A]/60 via-[#05050A]/95 to-[#05050A]"></div>
        @else
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-purple-900/20 rounded-full blur-[120px]"></div>
        @endif
    </div>

    {{-- 2. HERO SECTION --}}
    <div class="relative z-10 pt-10 md:pt-10 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start">
                
                {{-- Left: Poster Image --}}
                <div class="relative w-40 md:w-64 shrink-0 mx-auto md:mx-0 group">
                    <div class="absolute inset-0 bg-purple-600 rounded-2xl blur-2xl opacity-20 group-hover:opacity-40 transition duration-500"></div>
                    <div class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl ring-1 ring-white/10 transform group-hover:scale-[1.02] transition duration-500 bg-[#1a1a1a]">
                        <img src="{{ $anime->thumbnail_url ? $anime->thumbnail_url : 'https://via.placeholder.com/300x450' }}" 
                             class="w-full h-auto object-cover">
                    </div>
                </div>

                {{-- Right: Details --}}
                <div class="flex-1 text-center md:text-left w-full space-y-6">
                    <div>
                        <h1 class="text-3xl md:text-6xl font-black text-white mb-4 tracking-tight leading-none drop-shadow-2xl">
                            {{ $anime->title }}
                        </h1>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-300 backdrop-blur-sm">
                                {{ $anime->is_completed ? 'Completed' : 'Ongoing' }}
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-300 backdrop-blur-sm">
                                {{ $anime->seasons->count() }} Seasons
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-300 backdrop-blur-sm">
                                {{ $anime->total_episodes }} Episodes
                            </span>
                        </div>
                    </div>

                    <div x-data="{ expanded: false }" class="max-w-3xl mx-auto md:mx-0">
                        <p class="text-slate-300 text-sm md:text-lg leading-relaxed font-light transition-all duration-300"
                           :class="expanded ? '' : 'line-clamp-3'">
                            {{ $anime->description }}
                        </p>

                        @if(Str::length($anime->description) > 200)
                            <button @click="expanded = !expanded" 
                                    class="mt-2 text-xs md:text-sm font-bold text-purple-400 hover:text-purple-300 flex items-center gap-1 mx-auto md:mx-0 transition-colors cursor-pointer">
                                <span x-text="expanded ? 'See Less' : 'See More'"></span>
                                <svg x-show="!expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                        @endif
                    </div>
                    
                    <div class="flex items-center justify-center md:justify-start gap-4 pt-4">
                        @if($episodes->count() > 0)
                            <a href="{{ route('anime.watch', $episodes->first()->id) }}" wire:navigate
                               class="relative group px-8 py-3.5 rounded-xl bg-white text-black font-bold text-base md:text-lg shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)] transition transform active:scale-95 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-slate-200 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500"></div>
                                <div class="relative flex items-center gap-2">
                                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                    <span>Start Watching</span>
                                </div>
                            </a>
                        @endif

                        <button wire:click="toggleWatchlist" 
                            class="w-12 h-12 md:w-14 md:h-14 rounded-xl border border-white/10 flex items-center justify-center transition duration-300 backdrop-blur-md
                            {{ $isInWatchlist 
                                ? 'bg-pink-500/10 border-pink-500/50 text-pink-500 shadow-[0_0_15px_rgba(236,72,153,0.3)]' 
                                : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-5 h-5 md:w-6 md:h-6 {{ $isInWatchlist ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. EPISODE LIST SECTION --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 md:px-6 lg:px-8 mt-8">
        <div class="border-t border-white/5 pt-8 md:pt-10">
            
            {{-- Header & Season Tabs --}}
            <div class="flex flex-col gap-6 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 bg-purple-500 rounded-full shadow-[0_0_10px_rgba(168,85,247,0.8)]"></div>
                    <h3 class="text-xl font-bold text-white tracking-tight">Episodes</h3>
                </div>
                
                {{-- Season Selector --}}
                <div class="relative w-full group">
                    <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-[#05050A] to-transparent z-10 pointer-events-none md:hidden"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-[#05050A] to-transparent z-10 pointer-events-none md:hidden"></div>

                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x snap-mandatory scroll-smooth">
                        @foreach($anime->seasons as $season)
                            <button wire:click="selectSeason({{ $season->id }})"
                                class="relative px-5 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-300 shrink-0 snap-center border
                                {{ $currentSeason->id === $season->id 
                                    ? 'bg-white text-black border-white shadow-lg z-10' 
                                    : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white border-white/10' }}">
                                {{ $season->title }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ==============================================================
                 MOBILE VIEW: COMPACT NUMBER GRID (Phone Only)
                 ============================================================== --}}
            <div class="grid grid-cols-5 gap-2 md:hidden">
                @foreach($episodes as $episode)
                    @php
                        $isBought = in_array($episode->id, $unlockedEpisodeIds ?? []);
                        // $isActive ကို ဖြုတ်လိုက်ပါပြီ
                        
                        // Dynamic Classes for Mobile Buttons
                        $btnClass = "bg-[#15151a] text-slate-400 border-transparent hover:bg-white/10"; // Default Free
                        
                        if ($episode->is_premium) {
                            if ($isBought) {
                                $btnClass = "bg-[#15151a] text-cyan-400 border-cyan-500/50 shadow-[inset_0_0_10px_rgba(34,211,238,0.1)]"; // Owned
                            } else {
                                $btnClass = "bg-[#15151a] text-yellow-500 border-yellow-500/50 shadow-[inset_0_0_10px_rgba(234,179,8,0.1)]"; // Locked
                            }
                        }
                    @endphp

                    <a href="{{ route('anime.watch', $episode->id) }}" wire:navigate
                       class="relative aspect-square rounded-xl border flex flex-col items-center justify-center transition-all duration-200 active:scale-90 {{ $btnClass }}">
                        
                        <span class="text-lg font-bold">{{ $episode->episode_number }}</span>
                        
                        {{-- Status Icons (Tiny) --}}
                        @if($episode->is_premium && !$isBought)
                            <svg class="w-3 h-3 absolute bottom-1 right-1 opacity-80" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                        @elseif($isBought)
                            <svg class="w-3 h-3 absolute bottom-1 right-1 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- ==============================================================
                 DESKTOP VIEW: DETAILED LIST (Tablet/PC Only)
                 ============================================================== --}}
            <div class="hidden md:grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($episodes as $episode)
                    @php
                        $isBought = in_array($episode->id, $unlockedEpisodeIds ?? []);
                    @endphp

                    <a href="{{ route('anime.watch', $episode->id) }}" wire:navigate
                       class="group relative flex items-center justify-between p-3 rounded-xl bg-[#15151a] border border-white/5 hover:bg-[#1a1a20] hover:border-purple-500/30 transition-all duration-300 active:scale-[0.99]">
                        
                        <div class="flex items-center gap-4 min-w-0">
                            {{-- Episode Number Box --}}
                            <div class="shrink-0 w-12 h-12 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 font-bold group-hover:bg-purple-600 group-hover:text-white transition duration-300 shadow-inner">
                                <span class="text-base group-hover:hidden">{{ $episode->episode_number }}</span>
                                <svg class="w-5 h-5 hidden group-hover:block" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                            </div>

                            {{-- Info --}}
                            <div class="flex flex-col min-w-0">
                                <h4 class="text-base font-bold text-white truncate pr-2 group-hover:text-purple-400 transition">
                                    {{ $episode->title }}
                                </h4>
                                <span class="text-xs text-slate-500 truncate">24 min</span>
                            </div>
                        </div>

                        {{-- Right Side Status --}}
                        <div class="shrink-0 pl-2">
                            @if(!$episode->is_premium)
                                <span class="text-[10px] font-bold text-green-400 bg-green-500/10 px-2 py-1 rounded border border-green-500/20">FREE</span>
                            @elseif($isBought)
                                <span class="text-[10px] font-bold text-cyan-400 bg-cyan-500/10 px-2 py-1 rounded border border-cyan-500/20 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> OWNED
                                </span>
                            @else
                                <div class="flex flex-col items-end gap-0.5">
                                    <span class="text-[10px] font-bold text-yellow-400 bg-yellow-500/10 px-2 py-1 rounded border border-yellow-500/20 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path></svg>
                                        {{ $episode->coin_price }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>