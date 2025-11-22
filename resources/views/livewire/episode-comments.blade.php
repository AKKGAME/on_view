<div class="bg-slate-900/50 border-t border-slate-800 p-4 md:p-8 mt-8" 
     x-data="{ showDeleteModal: false, deletingId: null }"> {{-- 1. x-data ထည့်ထားပါတယ် --}}
    
    <div class="max-w-4xl mx-auto relative">
        
        <h3 class="text-xl font-bold text-white font-gaming mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-500 rounded-full"></span>
            Discussion Zone
        </h3>

        @auth
            <form wire:submit.prevent="postComment" class="mb-10 bg-slate-900 p-4 rounded-2xl border border-slate-800">
                {{-- Form logic --}}
                <div class="flex gap-4">
                    <div class="shrink-0 hidden md:block">
                        <div class="h-10 w-10 rounded-full bg-slate-800 flex items-center justify-center text-sm font-bold text-slate-400 border border-slate-700">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <textarea wire:model="body" rows="2" 
                                  placeholder="Share your thoughts about this episode..." 
                                  class="w-full bg-transparent border-0 text-white placeholder-slate-500 focus:ring-0 p-0 text-sm md:text-base resize-none"></textarea>
                        
                        <div class="flex justify-between items-center mt-4 pt-3 border-t border-slate-800">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="is_spoiler" class="peer sr-only">
                                    <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600 after:bg-white"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-500 group-hover:text-slate-300 transition">Contains Spoilers?</span>
                            </label>

                            <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-purple-900/20">
                                <span>Post</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 text-center mb-10">
                <p class="text-slate-400 text-sm mb-4">Login to join the discussion and earn XP!</p>
                <a href="{{ route('login') }}" class="inline-block bg-slate-800 hover:bg-slate-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition">Login to Comment</a>
            </div>
        @endauth

        <div class="space-y-6">
            @forelse($comments as $comment)
                <div class="flex gap-3 md:gap-4 group">
                    <div class="shrink-0">
                        <div class="h-10 w-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-sm font-bold text-slate-300 shadow-sm">
                            {{ substr($comment->user->name, 0, 1) }}
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="font-bold text-white text-sm">{{ $comment->user->name }}</span>
                            <span class="text-[10px] bg-blue-500/10 text-blue-400 px-1.5 py-0.5 rounded border border-blue-500/20 font-bold">Lvl {{ floor($comment->user->xp/1000)+1 }}</span>
                            
                            <div class="ml-auto flex items-center gap-3">
                                <span class="text-slate-600 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                                
                                @if(auth()->id() === $comment->user_id)
                                    <button @click="showDeleteModal = true; deletingId = {{ $comment->id }}" 
                                            class="text-slate-600 hover:text-red-500 transition p-1 rounded-lg hover:bg-red-500/10" 
                                            title="Delete Comment">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($comment->is_spoiler)
                            <div x-data="{ revealed: false }">
                                <button x-show="!revealed" @click="revealed = true" 
                                        class="bg-red-500/10 border border-red-500/20 text-red-400 px-3 py-2 rounded-lg text-xs font-bold flex items-center gap-2 hover:bg-red-500/20 transition w-full md:w-auto">
                                    <span>Spoiler Warning! Click to reveal</span>
                                </button>
                                <p x-show="revealed" class="text-slate-300 text-sm leading-relaxed bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                                    {{ $comment->body }}
                                </p>
                            </div>
                        @else
                            <p class="text-slate-300 text-sm leading-relaxed bg-slate-900/30 p-3 rounded-xl border border-slate-800/30 group-hover:border-slate-700/50 transition">
                                {{ $comment->body }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="text-2xl mb-2 opacity-30">💬</div>
                    <p class="text-slate-500 text-xs">No comments yet. Be the first to share your thoughts!</p>
                </div>
            @endforelse
            
            <div class="mt-4">
                {{ $comments->links() }}
            </div>
        </div>

        <div x-show="showDeleteModal" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center px-4 backdrop-blur-sm bg-black/80"
             x-transition.opacity.duration.300ms>
            
            <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-sm p-6 shadow-2xl transform transition-all"
                 @click.away="showDeleteModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                 
                <div class="text-center">
                    <div class="h-12 w-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4 text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    
                    <h3 class="text-lg font-bold text-white mb-2">Delete Comment?</h3>
                    <p class="text-slate-400 text-sm mb-6">
                        Are you sure you want to delete this comment? <br>This action cannot be undone.
                    </p>
                    
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false" 
                                class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 py-2.5 rounded-xl text-sm font-bold transition">
                            Cancel
                        </button>
                        
                        {{-- Trigger Livewire Delete --}}
                        <button @click="$wire.deleteComment(deletingId); showDeleteModal = false"
                                class="flex-1 bg-red-600 hover:bg-red-500 text-white py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-red-900/20">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>