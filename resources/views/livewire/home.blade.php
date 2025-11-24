<div class="min-h-screen pb-20 md:pb-10 bg-[#05050A] text-white font-sans selection:bg-purple-500 selection:text-white overflow-x-hidden">
    
    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- 1. HERO SLIDER SECTION --}}
    @if($sliderAnimes->count() > 0)
        <div x-data="{ 
                activeSlide: 0, 
                slides: {{ $sliderAnimes->count() }}, 
                startAutoplay() { this.autoplayInterval = setInterval(() => { this.next(); }, 6000); },
                stopAutoplay() { clearInterval(this.autoplayInterval); },
                next() { this.activeSlide = (this.activeSlide + 1) % this.slides; },
                prev() { this.activeSlide = (this.activeSlide - 1 + this.slides) % this.slides; },
                goTo(index) { this.activeSlide = index; }
             }" 
             x-init="startAutoplay()"
             @mouseenter="stopAutoplay()" 
             @mouseleave="startAutoplay()"
             class="relative w-full h-[65vh] md:h-[600px] md:rounded-3xl overflow-hidden mb-8 md:mb-12 group">
            
            @foreach($sliderAnimes as $index => $anime)
                <div x-show="activeSlide === {{ $index }}"
                     x-transition:enter="transition ease-in-out duration-1000"
                     x-transition:enter-start="opacity-0 scale-105"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in-out duration-1000"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute inset-0 w-full h-full">
                    
                    {{-- Background Image (Cover or Thumbnail) --}}
                    <img src="{{ $anime->cover_url ? $anime->cover_url : $anime->thumbnail_url }}" 
                         class="w-full h-full object-cover object-center opacity-80 md:opacity-100">
                    
                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-[#05050A] via-[#05050A]/60 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#05050A]/80 via-transparent to-transparent"></div>

                    {{-- Content --}}
                    <div class="absolute inset-0 flex flex-col justify-end md:justify-center p-6 md:p-12 z-10 pb-16 md:pb-12">
                        <div class="w-full md:max-w-2xl animate-fadeInUp">
                            {{-- Badges --}}
                            <div class="flex flex-wrap items-center gap-2 mb-3 md:mb-4">
                                <span class="px-2 py-1 bg-purple-600 text-white text-[10px] md:text-xs font-bold rounded-md uppercase tracking-wider shadow-lg shadow-purple-600/30">
                                    Featured #{{ $index + 1 }}
                                </span>
                                <span class="px-2 py-1 bg-white/10 backdrop-blur-md border border-white/10 text-white text-[10px] md:text-xs font-bold rounded-md">
                                    {{ $anime->is_completed ? 'Completed' : 'Ongoing' }}
                                </span>
                            </div>
                            
                            {{-- Title --}}
                            <h1 class="text-3xl md:text-6xl font-black text-white font-gaming mb-2 md:mb-4 leading-tight drop-shadow-2xl line-clamp-2">
                                {{ $anime->title }}
                            </h1>

                            {{-- Description (Desktop Only) --}}
                            <p class="text-slate-300 text-sm md:text-lg mb-6 hidden md:block line-clamp-2 max-w-xl drop-shadow-md font-medium">
                                {{ $anime->description ?? 'Join the adventure in this amazing anime series.' }}
                            </p>

                            {{-- Watch Button --}}
                            <div class="flex items-center gap-3 w-full md:w-auto mt-4 md:mt-0">
                                <a href="{{ route('anime.show', $anime->slug) }}" 
                                   class="flex-1 md:flex-none justify-center px-6 py-3 bg-white text-slate-950 font-bold rounded-xl hover:bg-purple-50 transition transform active:scale-95 flex items-center gap-2 shadow-[0_0_20px_rgba(255,255,255,0.2)]">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                    <span>Watch Now</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Dots Indicators --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-1.5">
                @foreach($sliderAnimes as $index => $anime)
                    <button @click="goTo({{ $index }})" 
                            class="h-1 rounded-full transition-all duration-300 shadow-sm"
                            :class="activeSlide === {{ $index }} ? 'w-6 bg-purple-500' : 'w-1.5 bg-white/30'">
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 2. CONTINUE WATCHING SECTION --}}
    @auth
        @if(isset($continueWatching) && count($continueWatching) > 0)
            <div class="mb-10 max-w-7xl mx-auto" x-data="{ showDeleteModal: false, animeToDelete: null }">
                
                <div class="px-4 md:px-0 mb-4">
                    <h2 class="text-lg md:text-2xl font-bold text-white font-gaming flex items-center gap-2">
                        <div class="w-1 h-5 md:h-6 bg-orange-500 rounded-full shadow-[0_0_10px_rgba(249,115,22,0.8)]"></div>
                        Continue Watching
                    </h2>
                </div>

                {{-- Mobile Optimized Scroll Container --}}
                <div class="flex gap-3 md:gap-4 overflow-x-auto pb-6 px-4 md:px-0 hide-scrollbar snap-x snap-mandatory scroll-smooth">
                    @foreach($continueWatching as $history)
                        @php
                            $anime = $history->episode->season->anime;
                            $episode = $history->episode;
                        @endphp
                        
                        <div class="relative group flex-shrink-0 snap-start w-[280px] md:w-80">
                            
                            {{-- Card Link --}}
                            <a href="{{ route('anime.watch', $episode->id) }}" wire:navigate 
                               class="block w-full h-36 md:h-40 rounded-xl overflow-hidden border border-white/10 bg-[#15151a] relative shadow-md active:scale-[0.98] transition-transform">
                                
                                <img src="{{ $anime->cover_url ? $anime->cover_url : ($anime->thumbnail_url ? $anime->thumbnail_url : 'https://via.placeholder.com/300x150') }}" 
                                     class="absolute inset-0 w-full h-full object-cover opacity-50">
                                
                                <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0e] via-[#0a0a0e]/80 to-transparent"></div>

                                <div class="absolute inset-0 p-4 flex flex-col justify-center pr-12">
                                    <h3 class="text-white font-bold truncate text-base md:text-lg font-gaming leading-tight">{{ $anime->title }}</h3>
                                    
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="bg-orange-500/20 text-orange-400 text-[10px] font-bold px-2 py-0.5 rounded border border-orange-500/20">
                                            EP {{ $episode->episode_number }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">{{ $history->updated_at->diffForHumans(null, true) }}</span>
                                    </div>

                                    <div class="flex items-center gap-1.5 mt-3 text-xs font-bold text-slate-300">
                                        <div class="w-6 h-6 rounded-full bg-orange-600 flex items-center justify-center text-white shadow-sm">
                                            <svg class="w-3 h-3 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                        </div>
                                        <span>Resume</span>
                                    </div>
                                </div>

                                <div class="absolute bottom-0 left-0 w-full h-1 bg-white/10">
                                    <div class="h-full bg-orange-500 w-[65%] shadow-[0_0_10px_rgba(249,115,22,0.8)]"></div>
                                </div>
                            </a>

                            {{-- Remove Button --}}
                            <button @click="showDeleteModal = true; animeToDelete = {{ $anime->id }}"
                                    class="absolute top-2 right-2 z-20 bg-black/40 text-slate-400 p-2 rounded-full backdrop-blur-sm border border-white/10 active:bg-red-600 active:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- DELETE CONFIRMATION MODAL --}}
                <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4">
                    <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                    <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-[#1a1a20] rounded-2xl border border-white/10 shadow-2xl w-full max-w-sm relative z-10 overflow-hidden p-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-3 text-red-500 border border-red-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 font-gaming">REMOVE HISTORY?</h3>
                        <p class="text-slate-400 text-xs mb-6 leading-relaxed">You won't be able to pick up where you left off for this anime.</p>
                        <div class="flex gap-3">
                            <button @click="showDeleteModal = false" class="flex-1 py-2.5 rounded-xl bg-white/5 text-slate-300 font-bold text-sm hover:bg-white/10 transition">Cancel</button>
                            <button @click="$wire.removeFromHistory(animeToDelete); showDeleteModal = false" class="flex-1 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-500 shadow-lg shadow-red-600/20 transition">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    {{-- 3. LATEST ARRIVALS SECTION --}}
    <div class="max-w-7xl mx-auto mt-8 md:mt-12">
        <div class="flex items-center justify-between mb-4 md:mb-6 px-4 md:px-0">
            <h2 class="text-lg md:text-3xl font-bold text-white font-gaming flex items-center gap-2">
                <div class="w-1 h-5 md:h-8 bg-purple-500 rounded-full shadow-[0_0_10px_rgba(168,85,247,0.8)]"></div>
                Latest Arrivals
            </h2>
            <a href="{{ route('explore') }}" class="text-xs md:text-sm text-purple-400 hover:text-white transition font-bold flex items-center gap-1 group">
                See All 
                <svg class="w-3 h-3 md:w-4 md:h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        
        <div class="relative group/slider">
            
            {{-- Fade Edges (Mobile Only) --}}
            <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-[#05050A] to-transparent z-10 pointer-events-none md:hidden"></div>
            <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-[#05050A] to-transparent z-10 pointer-events-none md:hidden"></div>

            {{-- Slider Container --}}
            <div class="flex gap-3 md:gap-5 overflow-x-auto pb-8 px-4 md:px-0 hide-scrollbar snap-x snap-mandatory scroll-smooth" id="latestSlider">
                @foreach($animes as $anime)
                    <a href="{{ route('anime.show', $anime->slug) }}" class="group relative block shrink-0 w-[140px] md:w-[200px] snap-start">
                        
                        <div class="aspect-[2/3] w-full overflow-hidden rounded-lg md:rounded-xl bg-slate-800 relative shadow-md border border-white/5 group-hover:border-purple-500/50 transition-all duration-300">
                            
                            <img src="{{ $anime->thumbnail_url ? $anime->thumbnail_url : 'https://via.placeholder.com/300x450' }}" 
                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-110 group-hover:opacity-60">
                            
                            {{-- Badge --}}
                            <div class="absolute top-1.5 right-1.5 md:top-2 md:right-2 bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded text-[9px] md:text-[10px] font-bold text-white border border-white/10 shadow-sm">
                                {{ $anime->total_episodes }} EP
                            </div>

                            {{-- Play Icon Overlay --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-purple-600 flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-300">
                                    <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 md:mt-3 px-0.5">
                            <h3 class="text-xs md:text-base font-bold text-white truncate group-hover:text-purple-400 transition">
                                {{ $anime->title }}
                            </h3>
                            <div class="flex items-center gap-2 text-[9px] md:text-xs text-slate-500 mt-0.5">
                                <span class="bg-white/5 px-1.5 py-0.5 rounded">{{ $anime->seasons->count() }} Seasons</span>
                                <span class="{{ $anime->is_completed ? 'text-green-400' : 'text-blue-400' }}">
                                    {{ $anime->is_completed ? 'Finished' : 'On Air' }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>