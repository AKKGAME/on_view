<div class="min-h-screen pb-20 relative bg-[#05050A] text-white font-sans selection:bg-purple-500 selection:text-white">


<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

    {{-- 1. AMBIENT BACKGROUND (Cinematic Feel) --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        @php
            $bgImage = $anime->cover_url 
                ? \Illuminate\Support\Facades\Storage::url($anime->cover_url) 
                : ($anime->thumbnail_url ? \Illuminate\Support\Facades\Storage::url($anime->thumbnail_url) : null);
        @endphp

        @if($bgImage)
            <div class="absolute inset-0">
                <img src="{{ $bgImage }}" class="w-full h-full object-cover opacity-[0.2] blur-[80px] scale-110">
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#05050A]/60 via-[#05050A]/90 to-[#05050A]"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#05050A] via-transparent to-[#05050A]"></div>
        @else
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-purple-900/20 rounded-full blur-[120px]"></div>
        @endif
    </div>

    {{-- 2. HERO SECTION (Info & Details) --}}
    <div class="relative z-10 pt-24 md:pt-32 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start">
                
                {{-- Left: Poster Image --}}
                <div class="relative w-48 md:w-72 shrink-0 mx-auto md:mx-0 group perspective-1000">
                    <div class="absolute inset-0 bg-purple-600 rounded-2xl blur-2xl opacity-20 group-hover:opacity-40 transition duration-500"></div>
                    <div class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl ring-1 ring-white/10 transform group-hover:scale-[1.02] group-hover:-rotate-1 transition duration-500 bg-[#1a1a1a]">
                        <img src="{{ $anime->thumbnail_url ? \Illuminate\Support\Facades\Storage::url($anime->thumbnail_url) : 'https://via.placeholder.com/300x450' }}" 
                             class="w-full h-auto object-cover">
                    </div>
                </div>

                {{-- Right: Details --}}
                <div class="flex-1 text-center md:text-left w-full space-y-6">
                    
                    {{-- Title & Badges --}}
                    <div>
                        <h1 class="text-4xl md:text-6xl font-black text-white mb-4 tracking-tight leading-none drop-shadow-2xl">
                            {{ $anime->title }}
                        </h1>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-xs font-bold uppercase tracking-wider text-slate-300 backdrop-blur-sm">
                                {{ $anime->is_completed ? 'Completed' : 'Ongoing' }}
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-xs font-bold uppercase tracking-wider text-slate-300 backdrop-blur-sm">
                                {{ $anime->seasons->count() }} Seasons
                            </span>
                            <span class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-xs font-bold uppercase tracking-wider text-slate-300 backdrop-blur-sm">
                                {{ $anime->total_episodes }} Episodes
                            </span>
                        </div>
                    </div>

                    {{-- Description (See More / See Less Logic) --}}
                    <div x-data="{ expanded: false }" class="max-w-3xl mx-auto md:mx-0">
                        <p class="text-slate-300 text-base md:text-lg leading-relaxed font-light transition-all duration-300"
                           :class="expanded ? '' : 'line-clamp-3'">
                            {{ $anime->description }}
                        </p>

                        {{-- Only show button if description is longer than 200 characters --}}
                        @if(Str::length($anime->description) > 200)
                            <button @click="expanded = !expanded" 
                                    class="mt-2 text-sm font-bold text-purple-400 hover:text-purple-300 flex items-center gap-1 mx-auto md:mx-0 transition-colors cursor-pointer">
                                <span x-text="expanded ? 'See Less' : 'See More'"></span>
                                <svg x-show="!expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <svg x-show="expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                            </button>
                        @endif
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-center md:justify-start gap-4 pt-4">
                        @if($episodes->count() > 0)
                            {{-- Link to First Episode --}}
                            <a href="{{ route('anime.watch', $episodes->first()->id) }}" wire:navigate
                               class="relative group px-8 py-4 rounded-xl bg-white text-black font-bold text-lg shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)] transition transform active:scale-95 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-slate-200 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500"></div>
                                <div class="relative flex items-center gap-2">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                    <span>Start Watching</span>
                                </div>
                            </a>
                        @endif

                        {{-- Watchlist Button --}}
                        <button wire:click="toggleWatchlist" 
                            class="w-14 h-14 rounded-xl border border-white/10 flex items-center justify-center transition duration-300 backdrop-blur-md
                            {{ $isInWatchlist 
                                ? 'bg-pink-500/10 border-pink-500/50 text-pink-500 shadow-[0_0_15px_rgba(236,72,153,0.3)]' 
                                : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-6 h-6 {{ $isInWatchlist ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. EPISODE LIST SECTION --}}
    <div class="relative z-10 max-w-7xl mx-auto px-0 md:px-6 lg:px-8 mt-8"> <div class="border-t border-white/5 pt-8 md:pt-10">
            
            {{-- Header & Season Tabs Container --}}
            <div class="flex flex-col gap-6 mb-8">
                
                {{-- Title (With Decorative Line) --}}
                <!-- <div class="flex items-center gap-3 px-4 md:px-0">
                    <div class="w-1 h-8 bg-gradient-to-b from-purple-500 to-blue-500 rounded-full shadow-[0_0_10px_rgba(168,85,247,0.8)]"></div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">Episodes</h3>
                </div> -->
                
                {{-- Mobile-Optimized Season Selector --}}
                <div class="relative w-full group">
                    
                    {{-- Fade Gradients (Mobile Only Hints) --}}
                    <div class="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-[#05050A] to-transparent z-10 pointer-events-none md:hidden"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-[#05050A] to-transparent z-10 pointer-events-none md:hidden"></div>

                    {{-- Scrollable List --}}
                    <div class="flex gap-3 overflow-x-auto p-4 md:px-4 scrollbar-hide snap-x snap-mandatory scroll-smooth">
                        @foreach($anime->seasons as $season)
                            <button wire:click="selectSeason({{ $season->id }})"
                                class="relative px-6 py-3 rounded-full text-sm font-bold whitespace-nowrap transition-all duration-300 shrink-0 snap-center border
                                {{ $currentSeason->id === $season->id 
                                    ? 'bg-white text-black border-white shadow-[0_0_20px_rgba(255,255,255,0.3)] scale-105 z-10' 
                                    : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white border-white/10' }}">
                                
                                {{-- Active Indicator Dot --}}
                                @if($currentSeason->id === $season->id)
                                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
                                    </span>
                                @endif

                                {{ $season->title }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Episode Grid --}}
            <div class="px-4 md:px-0 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($episodes as $episode)
                    @php
                        $isBought = in_array($episode->id, $unlockedEpisodeIds ?? []);
                    @endphp

                    <a href="{{ route('anime.watch', $episode->id) }}" wire:navigate
                       class="group relative p-1 rounded-2xl transition-all duration-300 cursor-pointer hover:scale-[1.02] active:scale-95">
                        
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-purple-500 to-blue-500 opacity-0 group-hover:opacity-50 blur-sm transition duration-500"></div>

                        <div class="relative h-full bg-[#0e0e12] rounded-xl p-4 border border-white/5 group-hover:border-white/10 flex items-center gap-4 z-10">
                            
                            <div class="shrink-0 w-14 h-14 rounded-xl bg-white/5 flex items-center justify-center border border-white/5 group-hover:bg-purple-600/20 group-hover:border-purple-500/30 transition">
                                <span class="text-sm font-black text-slate-400 group-hover:text-purple-400 transition">
                                    {{ $episode->episode_number }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-sm text-slate-200 group-hover:text-white truncate transition">
                                    {{ $episode->title }}
                                </h4>
                                
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded 
                                        {{ $episode->is_premium ? 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20' : 'bg-green-500/10 text-green-500 border border-green-500/20' }}">
                                        {{ $episode->is_premium ? 'PREMIUM' : 'FREE' }}
                                    </span>

                                    @if($episode->is_premium && !$isBought)
                                        <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    @elseif($isBought)
                                        <svg class="w-3 h-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 opacity-0 group-hover:opacity-100 transition -translate-x-2 group-hover:translate-x-0">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
