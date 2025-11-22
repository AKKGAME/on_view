<div class="relative" x-data="{ open: false, showClearModal: false }" @click.outside="open = false">
    
    <button @click="open = !open" class="relative p-2 rounded-xl hover:bg-white/10 transition group">
        <svg class="w-6 h-6 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-[#0B0E14] animate-pulse"></span>
        @endif
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak
         class="absolute right-0 mt-2 w-80 md:w-96 bg-[#0B0E14]/95 backdrop-blur-xl border border-slate-800 rounded-2xl shadow-2xl overflow-hidden z-50">
        
        <div class="px-4 py-3 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
            <h3 class="text-white font-bold font-gaming text-sm">Notifications</h3>
            
            @if($notifications->count() > 0)
                <button @click="showClearModal = true; open = false" 
                        class="text-[10px] text-slate-400 hover:text-red-400 font-bold uppercase flex items-center gap-1 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Clear All
                </button>
            @endif
        </div>

        <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
            @forelse($notifications as $notify)
                <div class="group relative px-4 py-3 border-b border-slate-800/50 hover:bg-white/5 transition flex gap-3 {{ $notify->read_at ? 'opacity-60' : 'opacity-100 bg-white/[0.02]' }}">
                    
                    <div class="shrink-0 mt-1" wire:click="markAsRead('{{ $notify->id }}')">
                        @if($notify->data['type'] == 'success')
                            <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center text-green-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                        @elseif($notify->data['type'] == 'error')
                            <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        @endif
                    </div>

                    <div class="flex-1 cursor-pointer" wire:click="markAsRead('{{ $notify->id }}')">
                        <h4 class="text-sm font-bold text-white {{ !$notify->read_at ? 'text-purple-200' : '' }}">{{ $notify->data['title'] }}</h4>
                        <p class="text-xs text-slate-400 mt-0.5 leading-snug">{{ $notify->data['message'] }}</p>
                        <p class="text-[10px] text-slate-600 mt-1">{{ $notify->created_at->diffForHumans() }}</p>
                    </div>

                    <button wire:click="delete('{{ $notify->id }}')" 
                            class="absolute top-3 right-3 p-1 text-slate-600 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    @if(!$notify->read_at)
                        <div class="absolute bottom-3 right-3 w-1.5 h-1.5 rounded-full bg-purple-500"></div>
                    @endif
                </div>
            @empty
                <div class="py-10 text-center flex flex-col items-center justify-center">
                    <div class="w-12 h-12 bg-slate-800/50 rounded-full flex items-center justify-center mb-3 text-2xl opacity-50">🔕</div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">No notifications</p>
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="showClearModal" 
         x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4 h-screen w-screen">
        
        <div x-show="showClearModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/90 backdrop-blur-md" 
             @click="showClearModal = false"></div>

        <div x-show="showClearModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="bg-slate-900 rounded-2xl border border-slate-700 shadow-[0_0_50px_rgba(0,0,0,0.5)] w-full max-w-sm relative z-10 overflow-hidden p-6 text-center">
            
            <div class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4 text-red-500 border border-red-500/20 shadow-[0_0_20px_rgba(239,68,68,0.2)] animate-bounce">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>

            <h3 class="text-xl font-bold text-white font-gaming mb-2 tracking-wide">CLEAR ALL?</h3>
            <p class="text-slate-400 text-sm mb-6 leading-relaxed">This will remove all your notifications permanently. <br>Are you sure?</p>

            <div class="flex gap-3">
                <button @click="showClearModal = false" class="flex-1 py-3 rounded-xl bg-slate-800 text-slate-300 font-bold hover:bg-slate-700 transition text-sm border border-slate-700">
                    Cancel
                </button>
                <button wire:click="clearAll" @click="showClearModal = false" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-500 transition shadow-lg shadow-red-600/20 text-sm">
                    Yes, Clear
                </button>
            </div>
        </div>
    </div>

</div>