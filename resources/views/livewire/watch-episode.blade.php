<div class="min-h-screen bg-[#05050A] text-white pb-20 relative overflow-x-hidden font-sans selection:bg-purple-500 selection:text-white">

    <style>
        /* Plyr Custom Theme (Purple) */
        :root { --plyr-color-main: #a855f7; }
        .plyr--full-ui input[type=range] { color: #a855f7; }
        .plyr__control--overlaid { background: rgba(168, 85, 247, 0.8); }
        .plyr__control:hover { background: #a855f7; }
        .plyr { border-radius: 12px; overflow: hidden; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>

    {{-- 1. AMBIENT BACKGROUND --}}
    <div class="fixed inset-0 z-0 pointer-events-none">
        @php
            $bgImage = $anime->cover_url 
                ? \Illuminate\Support\Facades\Storage::url($anime->cover_url) 
                : ($anime->thumbnail_url ? \Illuminate\Support\Facades\Storage::url($anime->thumbnail_url) : null);
        @endphp

        @if($bgImage)
            <div class="absolute inset-0">
                <img src="{{ $bgImage }}" class="w-full h-full object-cover opacity-[0.15] blur-[100px] scale-110">
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#05050A]/80 via-[#05050A]/95 to-[#05050A]"></div>
        @else
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-purple-900/20 rounded-full blur-[120px]"></div>
        @endif
    </div>

    {{-- 2. TOP NAVIGATION --}}
    <div class="relative z-20 px-4 py-3 md:py-4 border-b border-white/5 bg-[#05050A]/80 backdrop-blur-md sticky top-0">
        <div class="max-w-[1800px] mx-auto flex items-center justify-between">
            <a href="{{ route('anime.show', $anime->slug) }}" wire:navigate 
               class="flex items-center gap-3 text-slate-400 hover:text-white transition group">
                <div class="p-2 rounded-full bg-white/5 group-hover:bg-white/10 transition">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Back to</span>
                    <span class="font-bold text-sm md:text-base leading-none line-clamp-1">{{ $anime->title }}</span>
                </div>
            </a>
            <div class="md:hidden">
                <span class="px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-xs font-bold">EP {{ $episode->episode_number }}</span>
            </div>
        </div>
    </div>

    {{-- 3. MAIN CONTENT WRAPPER --}}
    <div class="relative z-10 max-w-[1800px] mx-auto">
        
        <div class="flex flex-col lg:flex-row">
            
            {{-- LEFT COLUMN: PLAYER & INFO --}}
            <div class="flex-1 w-full min-w-0 lg:py-8 lg:pl-8 lg:pr-6">
                
                {{-- VIDEO PLAYER SECTION --}}
                <div class="relative group w-full px-4 lg:px-0 mt-4 lg:mt-0">
                    <div class="hidden lg:block absolute -inset-1 bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                    
                    <div class="relative w-full bg-black rounded-xl lg:rounded-2xl overflow-hidden shadow-2xl lg:ring-1 ring-white/10 z-10 aspect-video">
                        @if($isUnlocked)
                            {{-- Plyr Player Container --}}
                            <div class="w-full h-full" wire:ignore>
                                @if(Str::contains($episode->video_url, ['youtube.com', 'youtu.be']))
                                    {{-- YouTube --}}
                                    <div id="player" data-plyr-provider="youtube" data-plyr-embed-id="{{ $episode->video_url }}"></div>
                                @elseif(Str::contains($episode->video_url, ['vimeo.com']))
                                    {{-- Vimeo --}}
                                    <div id="player" data-plyr-provider="vimeo" data-plyr-embed-id="{{ $episode->video_url }}"></div>
                                @elseif(Str::endsWith($episode->video_url, ['.mp4', '.mkv', '.webm']))
                                    {{-- Direct File --}}
                                    <video id="player" playsinline controls>
                                        <source src="{{ route('stream.play', $episode->id) }}" type="video/mp4" />
                                    </video>
                                @else
                                    {{-- Fallback to Iframe for other sources --}}
                                    <iframe src="{{ $episode->video_url }}" class="w-full h-full" allowfullscreen></iframe>
                                @endif
                            </div>
                        @else
                            {{-- LOCKED UI --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center bg-[#0a0a0a] text-center p-4" 
                                 x-data="{ showConfirm: false }">
                                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10 pointer-events-none"></div>
                                
                                <div x-show="!showConfirm" class="relative z-10 flex flex-col items-center max-w-sm">
                                    <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-black border border-yellow-500/30 flex items-center justify-center shadow-2xl mb-3 lg:mb-4 animate-pulse">
                                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <h2 class="text-xl lg:text-2xl font-black text-white mb-1">PREMIUM CONTENT</h2>
                                    <p class="text-slate-400 text-xs lg:text-sm mb-6">Unlock this episode to watch.</p>

                                    @auth
                                        <button @click="showConfirm = true" class="w-full py-3 px-6 bg-yellow-500 hover:bg-yellow-400 text-black font-bold rounded-xl transition transform active:scale-95 shadow-lg shadow-yellow-500/20 text-sm lg:text-base">
                                            UNLOCK ({{ $episode->coin_price }} G)
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="w-full py-3 px-6 bg-white/10 text-white font-bold rounded-xl border border-white/10 block text-sm lg:text-base">Login Required</a>
                                    @endauth
                                </div>

                                <div x-show="showConfirm" style="display: none;" class="relative z-20 bg-[#15151a] border border-white/10 p-6 rounded-2xl shadow-2xl w-full max-w-xs">
                                    <h3 class="text-lg font-bold text-white mb-1">Confirm?</h3>
                                    <p class="text-slate-400 text-xs mb-4">Cost: <span class="text-yellow-500">{{ $episode->coin_price }} Coins</span></p>
                                    <div class="flex gap-2">
                                        <button @click="showConfirm = false" class="flex-1 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-xs font-bold text-slate-300">Cancel</button>
                                        <button wire:click="unlockEpisode" class="flex-1 py-2 bg-yellow-500 hover:bg-yellow-400 rounded-lg text-xs font-bold text-black flex items-center justify-center gap-1">
                                            <span wire:loading.remove>Confirm</span>
                                            <span wire:loading class="animate-spin border-2 border-black border-t-transparent rounded-full w-3 h-3"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- CONTROLS & TITLE --}}
                <div class="mt-4 px-4 lg:px-0 flex flex-col md:flex-row gap-4 justify-between items-start">
                    <div class="w-full md:w-auto">
                        <h1 class="text-lg lg:text-2xl font-bold text-white leading-snug line-clamp-2">{{ $episode->title }}</h1>
                        <div class="flex items-center gap-2 mt-1.5 text-xs md:text-sm text-slate-400 font-medium">
                            <span class="bg-white/5 px-2 py-0.5 rounded border border-white/5">EP {{ $episode->episode_number }}</span>
                            <span class="w-1 h-1 bg-slate-600 rounded-full"></span>
                            <span>{{ $anime->title }}</span>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-auto flex items-center gap-2 bg-[#1a1a1a] p-1 rounded-xl border border-white/5">
                        @php
                            $prev = $playlist->where('episode_number', '<', $episode->episode_number)->last();
                            $next = $playlist->where('episode_number', '>', $episode->episode_number)->first();
                        @endphp

                        <button @if($prev) wire:click="$dispatch('link-navigate', {href: '{{ route('anime.watch', $prev->id) }}'})" @else disabled @endif
                           class="flex-1 md:flex-none px-4 py-2.5 rounded-lg hover:bg-white/10 text-white font-bold text-sm disabled:opacity-30 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                           <span class="md:hidden lg:inline">Prev</span>
                        </button>

                        <div class="w-px h-5 bg-white/10"></div>

                        <button @if($next) wire:click="$dispatch('link-navigate', {href: '{{ route('anime.watch', $next->id) }}'})" @else disabled @endif
                           class="flex-1 md:flex-none px-6 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm disabled:opacity-30 disabled:bg-white/5 disabled:cursor-not-allowed transition flex items-center justify-center gap-2 shadow-lg shadow-purple-900/20">
                           Next <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- DESKTOP COMMENTS --}}
                <div class="hidden lg:block mt-10 border-t border-white/5 pt-8">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="text-purple-500">#</span> Discussion
                    </h3>
                    @livewire('episode-comments', ['episodeId' => $episode->id])
               </div>
            </div>

            {{-- RIGHT COLUMN: PLAYLIST --}}
            <div class="w-full lg:w-[380px] shrink-0 lg:py-8 lg:pr-8 flex flex-col">
                <div class="mt-6 lg:mt-0 px-4 lg:px-0">
                    <div class="bg-[#0f0f13] border border-white/5 rounded-xl lg:rounded-2xl overflow-hidden flex flex-col shadow-xl lg:sticky lg:top-24 h-auto max-h-[400px] lg:max-h-[calc(100vh-120px)]">
                        <div class="p-4 border-b border-white/5 bg-[#131318] flex justify-between items-center shrink-0">
                            <h3 class="font-bold text-white text-base lg:text-lg">Up Next</h3>
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $playlist->count() }} EPS</span>
                        </div>
                        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar" 
                             x-data x-init="$el.querySelector('.active-episode')?.scrollIntoView({ block: 'center', behavior: 'smooth' })">
                            @foreach($playlist as $ep)
                                @php $isActive = $ep->id === $episode->id; @endphp
                                <a href="{{ route('anime.watch', $ep->id) }}" wire:navigate
                                   class="group flex items-center gap-3 p-2 rounded-lg cursor-pointer transition border border-transparent
                                   {{ $isActive ? 'bg-purple-600/10 border-purple-500/50 active-episode' : 'hover:bg-white/5 hover:border-white/5' }}">
                                    <div class="relative w-24 lg:w-28 aspect-video bg-slate-800 rounded-md overflow-hidden shrink-0 flex items-center justify-center group-hover:ring-1 ring-white/20 transition">
                                        <span class="text-[10px] lg:text-xs font-bold text-slate-500">EP {{ $ep->episode_number }}</span>
                                        @if($isActive)
                                            <div class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-[1px]">
                                                <div class="flex items-end gap-[2px] h-3">
                                                    <div class="w-[2px] bg-purple-400 animate-[bounce_1s_infinite] h-2"></div>
                                                    <div class="w-[2px] bg-purple-400 animate-[bounce_1.2s_infinite] h-3"></div>
                                                    <div class="w-[2px] bg-purple-400 animate-[bounce_0.8s_infinite] h-1"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs lg:text-sm font-bold truncate transition {{ $isActive ? 'text-purple-300' : 'text-slate-300 group-hover:text-white' }}">{{ $ep->title }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $ep->is_premium ? 'bg-yellow-500/10 text-yellow-500' : 'bg-green-500/10 text-green-500' }}">{{ $ep->is_premium ? 'PREMIUM' : 'FREE' }}</span>
                                            @if($isActive)<span class="text-[9px] font-bold text-purple-400 uppercase tracking-wider">Playing</span>@endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="lg:hidden px-4 mt-8 border-t border-white/5 pt-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><span class="text-purple-500">#</span> Comments</h3>
                    @livewire('episode-comments', ['episodeId' => $episode->id])
                </div>
            </div>
        </div>
    </div>

{{-- Initialize Plyr --}}
<script>
    document.addEventListener('livewire:navigated', () => {
        const player = new Plyr('#player', {
            controls: ['play-large', 'auto-play', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
            theme: '#a855f7',
        });
    });
</script>
</div>

