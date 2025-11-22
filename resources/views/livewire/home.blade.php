<div class="min-h-screen pb-24 md:pb-10">
    
    @if($sliderAnimes->count() > 0)
        <!-- HERO SLIDER SECTION -->
        <div x-data="{ 
                activeSlide: 0, 
                slides: {{ $sliderAnimes->count() }}, 
                autoplayInterval: null,
                startAutoplay() {
                    this.autoplayInterval = setInterval(() => {
                        this.next();
                    }, 6000); // 6 Seconds
                },
                stopAutoplay() {
                    clearInterval(this.autoplayInterval);
                },
                next() {
                    this.activeSlide = (this.activeSlide + 1) % this.slides;
                },
                prev() {
                    this.activeSlide = (this.activeSlide - 1 + this.slides) % this.slides;
                },
                goTo(index) {
                    this.activeSlide = index;
                }
             }" 
             x-init="startAutoplay()"
             @mouseenter="stopAutoplay()" 
             @mouseleave="startAutoplay()"
             class="relative w-full h-[70vh] md:h-[600px] md:rounded-3xl overflow-hidden mb-8 md:mb-12 group">
            
            <!-- SLIDES LOOP -->
            @foreach($sliderAnimes as $index => $anime)
                <div x-show="activeSlide === {{ $index }}"
                     x-transition:enter="transition ease-in-out duration-1000"
                     x-transition:enter-start="opacity-0 scale-105"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in-out duration-1000"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute inset-0 w-full h-full">
                    
                    <!-- Background Image -->
                    <img src="{{ $anime->cover_url ? asset('storage/' . $anime->cover_url) : asset('storage/' . $anime->thumbnail_url) }}" 
                         class="w-full h-full object-cover object-center">
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/30 to-transparent"></div>

                    <!-- Content -->
                    <div class="absolute inset-0 flex flex-col md:flex-row justify-end md:justify-between items-start md:items-center p-5 md:p-12 z-10">
                        
                        <!-- Left Info -->
                        <div class="w-full md:max-w-2xl mb-8 md:mb-0 animate-fadeInUp">
                            <div class="flex flex-wrap items-center gap-2 mb-3 md:mb-4">
                                <span class="px-2 py-1 md:px-3 bg-purple-600 text-white text-[10px] md:text-xs font-bold rounded-md uppercase tracking-wider shadow-lg shadow-purple-600/30">
                                    Trending #{{ $index + 1 }}
                                </span>
                                <span class="px-2 py-1 bg-slate-800/80 backdrop-blur-md border border-slate-700 text-white text-[10px] md:text-xs font-bold rounded-md">
                                    {{ $anime->is_completed ? 'Completed' : 'Ongoing' }}
                                </span>
                            </div>
                            
                            <h1 class="text-3xl md:text-6xl font-bold text-white font-gaming mb-2 md:mb-4 leading-tight drop-shadow-xl">
                                {{ $anime->title }}
                            </h1>

                            <p class="text-slate-300 text-xs md:text-lg mb-6 md:mb-8 line-clamp-2 md:line-clamp-3 max-w-xl drop-shadow-md font-medium">
                                {{ $anime->description ?? 'Join the adventure in this amazing anime series. Watch now and earn XP!' }}
                            </p>

                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <a href="{{ route('anime.show', $anime->slug) }}" 
                                   class="flex-1 md:flex-none justify-center px-6 py-3 md:py-3 bg-white text-slate-950 font-bold rounded-xl hover:bg-purple-50 transition transform active:scale-95 flex items-center gap-2 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                    <span>Watch Now</span>
                                </a>
                                
                                <div class="hidden md:flex items-center gap-2 text-slate-300 text-sm font-bold bg-slate-900/50 px-4 py-3 rounded-xl backdrop-blur-md border border-slate-700">
                                    <span>{{ $anime->total_episodes }} Episodes</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

            <!-- STATIC OVERLAY ELEMENTS (Don't slide) -->
            
            <!-- Daily Reward Widget (Desktop Position) -->
            @auth
                <div class="hidden md:block absolute right-12 top-1/2 -translate-y-1/2 z-20 w-80">
                    <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-2xl overflow-hidden transform hover:-translate-y-1 transition duration-300">
                        @livewire('daily-reward')
                    </div>
                </div>
            @endauth

            <!-- Slider Controls (Arrows) -->
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-3 rounded-full bg-black/30 hover:bg-purple-600/80 text-white backdrop-blur-md border border-white/10 transition opacity-0 group-hover:opacity-100 md:group-hover:translate-x-0 md:-translate-x-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-3 rounded-full bg-black/30 hover:bg-purple-600/80 text-white backdrop-blur-md border border-white/10 transition opacity-0 group-hover:opacity-100 md:group-hover:translate-x-0 md:translate-x-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Dots Indicators -->
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                @foreach($sliderAnimes as $index => $anime)
                    <button @click="goTo({{ $index }})" 
                            class="h-1.5 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $index }} ? 'w-8 bg-purple-500' : 'w-2 bg-slate-600 hover:bg-slate-400'">
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Mobile Daily Reward (Below Slider) -->
        @auth
            <div class="md:hidden px-4 mb-8">
                <div class="bg-slate-900 rounded-xl border border-slate-800 shadow-lg overflow-hidden">
                    @livewire('daily-reward')
                </div>
            </div>
        @endauth
    @endif

    {{-- CONTINUE WATCHING SECTION --}}
    @auth
        @if(isset($continueWatching) && count($continueWatching) > 0)
            <div class="mb-10 px-4 md:px-0" x-data="{ showDeleteModal: false, animeToDelete: null }">
                
                <h2 class="text-lg md:text-2xl font-bold text-white font-gaming flex items-center gap-2 mb-4">
                    <span class="w-1 h-6 bg-orange-500 rounded-full"></span>
                    Continue Watching
                </h2>

                <div class="flex gap-4 overflow-x-auto pb-4 hide-scrollbar snap-x">
                    @foreach($continueWatching as $history)
                        @php
                            $anime = $history->episode->season->anime;
                            $episode = $history->episode;
                        @endphp
                        
                        <div class="relative group flex-shrink-0">
                            <!-- Card -->
                            <a href="{{ route('anime.show', $anime->slug) }}" class="block w-64 h-32 rounded-xl overflow-hidden border border-slate-800 hover:border-orange-500/50 transition snap-start relative">
                                <img src="{{ $anime->cover_url ? asset('storage/' . $anime->cover_url) : asset('storage/' . $anime->thumbnail_url) }}" 
                                     class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-60 group-hover:scale-105 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/60 to-transparent"></div>
                                <div class="absolute inset-0 p-4 flex flex-col justify-center pr-10">
                                    <h3 class="text-white font-bold truncate text-lg font-gaming">{{ $anime->title }}</h3>
                                    <p class="text-orange-400 text-xs font-bold uppercase tracking-wider mb-3">Ep {{ $episode->episode_number }}</p>
                                    <div class="flex items-center gap-2 text-xs text-slate-300 group-hover:text-white transition">
                                        <div class="w-8 h-8 rounded-full bg-orange-600 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                        </div>
                                        <span>Resume</span>
                                    </div>
                                </div>
                                <div class="absolute bottom-0 left-0 w-full h-1 bg-slate-800"><div class="h-full bg-orange-500" style="width: 75%"></div></div>
                            </a>
                            <!-- Remove Button -->
                            <button @click="showDeleteModal = true; animeToDelete = {{ $anime->id }}"
                                    class="absolute top-2 right-2 z-20 bg-black/40 hover:bg-red-600 text-slate-400 hover:text-white p-1.5 rounded-full backdrop-blur-sm border border-white/10 transition opacity-100 md:opacity-0 md:group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- CONFIRMATION MODAL -->
                <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4">
                    <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                    <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-slate-900 rounded-2xl border border-slate-700 shadow-2xl w-full max-w-sm relative z-10 overflow-hidden p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4 text-red-500 border border-red-500/20">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white font-gaming mb-2">REMOVE FROM HISTORY?</h3>
                        <p class="text-slate-400 text-sm mb-6">This will remove the anime from your "Continue Watching" list.</p>
                        <div class="flex gap-3">
                            <button @click="showDeleteModal = false" class="flex-1 py-3 rounded-xl bg-slate-800 text-slate-300 font-bold hover:bg-slate-700 transition text-sm">Cancel</button>
                            <button @click="$wire.removeFromHistory(animeToDelete); showDeleteModal = false" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-500 transition shadow-lg shadow-red-600/20 text-sm">Yes, Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- 2. LATEST ARRIVALS SECTION -->
    <div class="px-4 md:px-0">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h2 class="text-xl md:text-3xl font-bold text-white font-gaming flex items-center gap-2">
                <span class="w-1 h-6 md:h-8 bg-purple-500 rounded-full"></span>
                Latest Arrivals
            </h2>
            <a href="{{ route('explore') }}" class="text-xs md:text-sm text-purple-400 hover:text-white transition font-bold">See All</a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6">
            @foreach($animes as $anime)
                <a href="{{ route('anime.show', $anime->slug) }}" class="group relative block">
                    
                    <div class="aspect-[2/3] w-full overflow-hidden rounded-lg md:rounded-xl bg-slate-800 relative shadow-md group-hover:shadow-purple-500/20 transition duration-300">
                        
                        <img src="{{ $anime->thumbnail_url ? asset('storage/' . $anime->thumbnail_url) : 'https://via.placeholder.com/300x450' }}" 
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-110 group-hover:opacity-40">
                        
                        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded text-[10px] font-bold text-white border border-white/10">
                            {{ $anime->total_episodes }} EP
                        </div>

                        <div class="hidden md:flex absolute inset-0 flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                            <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-300">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 md:mt-3">
                        <h3 class="text-sm md:text-base font-bold text-white truncate group-hover:text-purple-400 transition">
                            {{ $anime->title }}
                        </h3>
                        <div class="flex items-center gap-2 text-[10px] md:text-xs text-slate-500 mt-0.5">
                            <span>{{ $anime->seasons->count() }} Seasons</span>
                            <span>•</span>
                            <span class="{{ $anime->is_completed ? 'text-green-400' : 'text-blue-400' }}">
                                {{ $anime->is_completed ? 'End' : 'On Air' }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        
        <div class="mt-8">
            {{ $animes->links() }}
        </div>
    </div>
</div>