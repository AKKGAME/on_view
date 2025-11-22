<div class="max-w-7xl mx-auto min-h-screen pb-28 md:pb-24 pt-8 px-4 md:px-6">
    
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-6 mb-8">
        <div class="w-full md:w-auto">
            <h1 class="text-3xl md:text-5xl font-bold text-white font-gaming tracking-wide flex items-center gap-3 mb-1">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-blue-500">EXPLORE</span>
                <span class="hidden md:block h-px w-16 bg-slate-800/50 mt-2"></span>
            </h1>
            <p class="text-slate-400 text-sm">Find your next adventure from our collection.</p>
        </div>

        <div class="relative w-full md:w-96 group z-10">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl blur opacity-20 group-hover:opacity-50 transition duration-500"></div>
            <div class="relative bg-slate-900 rounded-xl flex items-center border border-slate-800 group-hover:border-slate-700 transition">
                <svg class="w-5 h-5 text-slate-500 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search anime titles..." 
                       class="w-full bg-transparent border-none rounded-xl py-3.5 pl-3 pr-4 text-white placeholder-slate-500 focus:ring-0 outline-none font-medium">
                
                <div wire:loading wire:target="search" class="absolute right-4">
                    <svg class="animate-spin h-4 w-4 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 mb-8">
        
        <div class="flex-1 overflow-x-auto scrollbar-hide py-1 -mx-4 px-4 lg:mx-0 lg:px-0">
            <div class="flex gap-2.5">
                <button wire:click="$set('genre', '')" 
                        class="px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 border shadow-sm
                        {{ $genre === '' 
                            ? 'bg-purple-600 border-purple-500 text-white shadow-[0_0_15px_rgba(147,51,234,0.4)] scale-105' 
                            : 'bg-slate-900/80 border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-white hover:border-slate-600' }}">
                    All
                </button>
                @foreach($genres as $g)
                    <button wire:click="$set('genre', {{ $g->id }})" 
                            class="px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 border shadow-sm
                            {{ $genre == $g->id 
                                ? 'bg-blue-600 border-blue-500 text-white shadow-[0_0_15px_rgba(37,99,235,0.4)] scale-105' 
                                : 'bg-slate-900/80 border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-white hover:border-slate-600' }}">
                        {{ $g->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="shrink-0 relative w-full lg:w-56 z-0">
            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
            <select wire:model.live="sort" class="appearance-none w-full bg-slate-900/80 border border-slate-800 text-slate-300 text-sm font-bold rounded-xl focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 block p-3 pr-10 cursor-pointer hover:bg-slate-800 transition shadow-lg">
                <option value="latest">✨ Newest Arrivals</option>
                <option value="oldest">📅 Oldest First</option>
                <option value="az">🔤 Name (A-Z)</option>
                <option value="za">🔤 Name (Z-A)</option>
            </select>
        </div>
    </div>

    <div>
        
        <div wire:loading wire:target="search, genre, sort" class="w-full animate-fadeIn">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                @for ($i = 0; $i < 10; $i++) 
                    <div class="group relative block">
                        <div class="aspect-[2/3] w-full rounded-xl bg-slate-800/50 relative overflow-hidden border border-slate-800">
                            <div class="absolute inset-0 -translate-x-full animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-slate-700/10 to-transparent"></div>
                        </div>
                        <div class="mt-3 space-y-2">
                            <div class="h-4 bg-slate-800/50 rounded w-3/4 animate-pulse"></div>
                            <div class="h-3 bg-slate-800/30 rounded w-1/2 animate-pulse"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div wire:loading.remove wire:target="search, genre, sort">
            @if($animes->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                    @foreach($animes as $anime)
                        <a href="{{ route('anime.show', $anime->slug) }}" class="group relative block">
                            
                            <div class="aspect-[2/3] w-full overflow-hidden rounded-xl bg-slate-800 relative shadow-lg group-hover:shadow-purple-500/20 transition duration-300 border border-slate-800 group-hover:border-purple-500/50">
                                
                                <img src="{{ $anime->thumbnail_url ? asset('storage/' . $anime->thumbnail_url) : 'https://via.placeholder.com/300x450' }}" 
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-110 group-hover:opacity-40">
                                
                                <div class="hidden md:flex absolute inset-0 flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                    <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-300">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                    </div>
                                </div>

                                <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded text-[10px] font-bold text-white border border-white/10">
                                    {{ $anime->total_episodes }} EP
                                </div>

                                <div class="absolute bottom-0 left-0 w-full p-2 bg-gradient-to-t from-black/90 to-transparent">
                                    <div class="text-[10px] text-slate-300 truncate">
                                        {{ $anime->genres->pluck('name')->join(', ') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <h3 class="text-sm md:text-base font-bold text-white truncate group-hover:text-purple-400 transition">
                                    {{ $anime->title }}
                                </h3>
                                <div class="flex items-center gap-2 text-[10px] md:text-xs text-slate-500 mt-1">
                                    <span>{{ $anime->seasons->count() }} Seasons</span>
                                    <span>•</span>
                                    <span class="{{ $anime->is_completed ? 'text-green-400' : 'text-blue-400' }}">
                                        {{ $anime->is_completed ? 'Completed' : 'Ongoing' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12 px-4">
                    {{ $animes->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center bg-slate-900/30 rounded-3xl border border-slate-800 border-dashed">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center text-4xl mb-6 opacity-50">🔍</div>
                    <h3 class="text-xl md:text-2xl font-bold text-white mb-2">No Anime Found</h3>
                    <p class="text-slate-500 max-w-xs mx-auto">We couldn't find any anime matching your criteria.</p>
                    <button wire:click="$set('search', ''); $set('genre', '')" class="mt-6 px-6 py-2.5 rounded-xl bg-slate-800 text-white font-bold hover:bg-purple-600 transition border border-slate-700">
                        Clear Filters
                    </button>
                </div>
            @endif
        </div>

    </div>

</div>