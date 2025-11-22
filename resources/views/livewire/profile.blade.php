<div class="max-w-6xl mx-auto py-8 md:py-8 px-4 md:px-6 pb-28 md:pb-24" wire:init="loadData">
    
    <div class="relative mb-6 md:mb-8 group">
        <div class="h-40 md:h-64 w-full rounded-2xl md:rounded-3xl overflow-hidden relative shadow-lg">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900 to-blue-900">
                <div class="absolute inset-0 opacity-30 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-b from-transparent to-slate-950/90"></div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full px-4 md:px-6 pb-4 md:pb-6 flex flex-col md:flex-row items-end gap-4 md:gap-6 translate-y-[15%] md:translate-y-0">
            <div class="relative shrink-0">
                <div class="h-24 w-24 md:h-36 md:w-36 rounded-2xl md:rounded-3xl border-4 border-[#0B0E14] shadow-2xl overflow-hidden bg-slate-800">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6d28d9&color=fff&size=256" class="w-full h-full object-cover">
                </div>
                <div class="absolute bottom-1 right-1 md:bottom-2 md:right-2 w-4 h-4 md:w-5 md:h-5 bg-green-500 border-2 md:border-4 border-[#0B0E14] rounded-full"></div>
            </div>

            <div class="flex-1 text-left mb-1 md:mb-2 min-w-0 w-full">
                <h1 class="text-2xl md:text-4xl font-bold text-white font-gaming tracking-wide drop-shadow-md truncate">{{ $user->name }}</h1>
                <div class="flex flex-wrap items-center gap-2 md:gap-3 mt-1 md:mt-2">
                    <span class="px-2 py-0.5 md:px-3 rounded-full bg-purple-600/20 border border-purple-500/30 text-purple-400 text-[10px] md:text-xs font-bold uppercase tracking-wider">{{ $user->rank }}</span>
                    <span class="text-slate-500 text-[10px] md:text-xs font-medium flex items-center gap-1">Joined {{ $user->created_at->format('M Y') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 md:gap-3 mb-0 md:mb-2 w-full md:w-auto">
                <button wire:click="$set('showEditModal', true)" class="flex-1 md:flex-none justify-center px-4 py-2 md:py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs md:text-sm font-bold transition flex items-center gap-2 shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button wire:click="logout" class="flex-1 md:flex-none justify-center px-4 py-2 md:py-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-500 text-xs md:text-sm font-bold transition flex items-center gap-2 shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mt-12 md:mt-4">
        
        <div class="space-y-4 md:space-y-6">
            <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 rounded-2xl p-4 md:p-5 shadow-lg">
                <h3 class="text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-widest mb-3 md:mb-4">Player Stats</h3>
                <div class="space-y-3 md:space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg bg-yellow-500/10 flex items-center justify-center text-yellow-500"><svg class="w-5 h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.996 6 10 6c.002 0 .788.207 1.252.993C11.717 7.76 12 8.852 12 10c0 1.148-.283 2.24-.748 3.027C10.788 13.793 10.002 14 10 14c-.004 0-.792-.193-1.264-.973C8.283 12.24 8 11.148 8 10c0-1.148.283-2.24.736-3.021z" clip-rule="evenodd" /></svg></div>
                            <div><div class="text-[10px] text-slate-500 font-bold uppercase">Coins</div><div class="text-white font-bold font-gaming text-base md:text-lg">{{ number_format($user->coins) }}</div></div>
                        </div>
                        <a href="{{ route('topup') }}" class="text-[10px] md:text-xs bg-yellow-500 text-black px-3 py-1.5 rounded-lg font-bold hover:bg-yellow-400 transition shadow-lg shadow-yellow-500/20">+ Top Up</a>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 font-bold font-gaming text-base md:text-lg">{{ floor($user->xp / 1000) + 1 }}</div>
                            <div><div class="text-[10px] text-slate-500 font-bold uppercase">Level</div><div class="text-white font-bold font-gaming text-base md:text-lg">{{ $user->xp }} <span class="text-[10px] md:text-xs text-slate-600">XP</span></div></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .854.215 1.64.583 2.306M12 12h.01M19 12h.01M6 12h.01"></path></svg></div>
                            <div><div class="text-[10px] text-slate-500 font-bold uppercase">Account ID</div><div class="text-white font-bold text-sm tracking-wider">{{ $user->phone }}</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-900/80 to-slate-900 rounded-2xl p-4 md:p-5 border border-purple-500/30 relative overflow-hidden shadow-lg">
                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/20 blur-3xl rounded-full -mr-8 -mt-8"></div>
                <h3 class="text-white font-bold font-gaming flex items-center gap-2 mb-1 text-base md:text-lg"><span class="text-yellow-400">★</span> INVITE FRIENDS</h3>
                <p class="text-slate-300 text-[10px] md:text-xs mb-4">Share your code to earn free coins.</p>
                <div class="bg-black/30 rounded-xl p-1.5 flex items-center border border-white/10">
                    <div class="flex-1 text-center font-mono font-bold text-purple-300 tracking-widest text-sm md:text-base">{{ $user->referral_code }}</div>
                    <button onclick="navigator.clipboard.writeText('{{ $user->referral_code }}'); this.innerHTML = 'COPIED!';" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded-lg text-[10px] md:text-xs font-bold transition">COPY</button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            
            <div class="mb-8">
                <h3 class="text-lg md:text-xl font-bold text-white font-gaming mb-4 md:mb-6 flex items-center gap-3">
                    <span class="w-1 h-6 bg-blue-500 rounded-full"></span> Unlocked Content 
                    <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded-full">
                        @if($readyToLoad) {{ $libraryAnimes->count() }} @else ... @endif
                    </span>
                </h3>

                <div wire:loading class="w-full space-y-4 animate-fadeIn">
                    @for($i=0; $i<3; $i++)
                        <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 flex items-center gap-4 animate-pulse">
                            <div class="w-12 h-16 md:w-16 md:h-24 bg-slate-800 rounded-lg"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-slate-800 rounded w-1/2"></div>
                                <div class="h-3 bg-slate-800 rounded w-1/3"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                @if($readyToLoad)
                    @if($libraryAnimes->count() > 0)
                        <div class="space-y-3 md:space-y-4">
                            @foreach($libraryAnimes as $anime)
                                <div x-data="{ expanded: false }" class="bg-slate-900/50 rounded-2xl border border-slate-800 hover:border-slate-700 transition overflow-hidden">
                                    
                                    <div @click="expanded = !expanded" class="flex items-center gap-3 md:gap-4 p-3 md:p-4 cursor-pointer hover:bg-slate-800/50 transition active:bg-slate-800">
                                        <img src="{{ asset('storage/' . $anime->thumbnail_url) }}" class="w-12 h-16 md:w-16 md:h-24 object-cover rounded-lg shadow-lg bg-slate-800">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-base md:text-lg font-bold text-white font-gaming truncate">{{ $anime->title }}</h4>
                                            <div class="flex items-center gap-3 mt-1"><span class="text-[10px] md:text-xs text-slate-400">{{ $anime->seasons->sum(fn($s) => $s->episodes->count()) }} Owned</span></div>
                                        </div>
                                        <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 transition-transform duration-300" :class="expanded ? 'rotate-180 bg-slate-700 text-white' : ''"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                                    </div>

                                    <div x-show="expanded" x-collapse class="border-t border-slate-800 bg-slate-950/30 p-3 md:p-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($anime->seasons as $season)
                                                @foreach($season->episodes as $episode)
                                                    <a href="{{ route('anime.show', $anime->slug) }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-purple-500/50 hover:bg-slate-800 transition group active:scale-[0.98]">
                                                        <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-green-500/10 flex items-center justify-center text-green-500 group-hover:bg-green-500 group-hover:text-white transition"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg></div>
                                                        <div class="truncate text-xs md:text-sm font-medium text-slate-300 group-hover:text-white">{{ $episode->title }} <span class="block text-[10px] text-slate-500 font-normal">Ep {{ $episode->episode_number }} • {{ $season->title }}</span></div>
                                                    </a>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 md:py-16 bg-slate-900/30 rounded-3xl border border-slate-800 border-dashed">
                            <div class="w-14 h-14 md:w-16 md:h-16 bg-slate-800 rounded-full flex items-center justify-center text-2xl md:text-3xl mb-4 opacity-50">🎒</div>
                            <h4 class="text-white font-bold text-base md:text-lg">Library Empty</h4>
                            <p class="text-slate-500 text-xs md:text-sm mb-6 max-w-xs text-center px-4">You haven't unlocked any premium episodes yet.</p>
                            <a href="{{ route('home') }}" class="px-6 py-2 rounded-full bg-white text-slate-900 text-xs md:text-sm font-bold hover:bg-slate-200 transition">Explore Anime</a>
                        </div>
                    @endif
                @endif
            </div>

            <div class="mt-8 md:mt-10">
                <h3 class="text-lg md:text-xl font-bold text-white font-gaming mb-4 md:mb-6 flex items-center gap-3">
                    <span class="w-1 h-6 bg-pink-500 rounded-full"></span> My Watchlist 
                    <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded-full">
                         @if($readyToLoad) {{ $watchlist->count() }} @else ... @endif
                    </span>
                </h3>

                <div wire:loading class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4 animate-fadeIn">
                    @for($i=0; $i<3; $i++)
                        <div class="aspect-[2/3] rounded-xl bg-slate-800/50 animate-pulse border border-slate-800"></div>
                    @endfor
                </div>

                @if($readyToLoad)
                    @if($watchlist->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
                            @foreach($watchlist as $anime)
                                <a href="{{ route('anime.show', $anime->slug) }}" class="group relative block">
                                    <div class="aspect-[2/3] w-full overflow-hidden rounded-xl bg-slate-800 relative shadow-lg group-hover:shadow-pink-500/20 transition duration-300 border border-slate-800 group-hover:border-pink-500/50">
                                        <img src="{{ asset('storage/' . $anime->thumbnail_url) }}" 
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-110 opacity-90 group-hover:opacity-100">
                                        <div class="absolute top-1.5 right-1.5 md:top-2 md:right-2 bg-pink-600 text-white p-1 md:p-1.5 rounded-full shadow-lg">
                                            <svg class="w-2.5 h-2.5 md:w-3 md:h-3 fill-current" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                        </div>
                                    </div>
                                    <h4 class="mt-2 text-xs md:text-sm font-bold text-slate-300 truncate group-hover:text-white transition">{{ $anime->title }}</h4>
                                    <p class="text-[10px] text-slate-500">{{ $anime->total_episodes }} Episodes</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 md:py-10 bg-slate-900/30 rounded-2xl border border-slate-800 border-dashed">
                            <p class="text-slate-500 text-xs md:text-sm">Your watchlist is empty.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div x-data="{ show: @entangle('showEditModal') }" x-show="show" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4 pb-safe">
        <div x-show="show" class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="show = false"></div>
        <div x-show="show" x-transition class="bg-slate-900 rounded-2xl border border-slate-700 shadow-2xl w-full max-w-md relative z-10 overflow-hidden max-h-[80vh] overflow-y-auto">
            <div class="p-5 md:p-6">
                <h2 class="text-xl md:text-2xl font-bold text-white font-gaming mb-1">EDIT PROFILE</h2>
                <form wire:submit.prevent="updateProfile" class="space-y-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Display Name</label>
                        <input wire:model="editName" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-white focus:border-purple-500 outline-none transition text-sm">
                        @error('editName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Phone Number</label>
                        <input wire:model="editPhone" type="tel" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-white focus:border-purple-500 outline-none transition text-sm">
                        @error('editPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">New Password (Optional)</label>
                        <input wire:model="editPassword" type="password" placeholder="Leave blank to keep current" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-white focus:border-purple-500 outline-none transition text-sm">
                        @error('editPassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="flex-1 py-3 rounded-xl bg-slate-800 text-slate-300 font-bold hover:bg-slate-700 transition text-sm">Cancel</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-500 transition shadow-lg shadow-purple-600/20 text-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>