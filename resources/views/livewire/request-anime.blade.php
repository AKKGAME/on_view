<div class="max-w-5xl mx-auto py-8 px-4 md:px-6 pb-24">
    
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-bold text-white font-gaming tracking-wide mb-2">
            REQUEST <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">CENTER</span>
        </h1>
        <p class="text-slate-400 text-sm">Can't find your favorite anime? Request it here!</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-slate-900/80 backdrop-blur-xl p-6 md:p-8 rounded-3xl border border-slate-800 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-pink-600/10 blur-3xl rounded-full -mr-16 -mt-16 pointer-events-none"></div>

                <div class="flex items-center justify-between mb-6 relative z-10">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        New Request
                    </h3>
                    <div class="text-xs font-bold bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">
                        Cost: <span class="text-yellow-400">{{ $cost }} Coins</span>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6 relative z-10">
                    
                    <div>
                        <label class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2 block">Anime Title</label>
                        <input wire:model="title" type="text" placeholder="e.g. Solo Leveling Season 2" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-white focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition font-medium">
                        @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2 block">Additional Note (Optional)</label>
                        <textarea wire:model="note" rows="3" placeholder="Any specific season or movie?" 
                                  class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-white focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition font-medium resize-none"></textarea>
                    </div>

                    <div class="flex items-start gap-3 bg-slate-950/50 p-3 rounded-xl border border-slate-800/50 text-xs text-slate-400">
                        <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>Your request will be reviewed by our admins. Coins are non-refundable even if the request is rejected.</p>
                    </div>

                    <button type="submit" 
                            class="w-full py-4 rounded-xl bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white font-bold text-lg shadow-lg shadow-pink-600/20 transform hover:scale-[1.02] active:scale-95 transition flex items-center justify-center gap-2">
                        <span wire:loading.remove>Submit Request (-{{ $cost }} G)</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white font-gaming px-2 flex items-center justify-between">
                <span>My Requests</span>
                <span class="text-xs bg-slate-800 text-slate-400 px-2 py-1 rounded-full">{{ $myRequests->count() }}</span>
            </h3>

            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1 custom-scrollbar">
                @foreach($myRequests as $req)
                    <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 hover:border-slate-700 transition relative overflow-hidden group">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1 
                            {{ $req->status === 'completed' ? 'bg-green-500' : ($req->status === 'rejected' ? 'bg-red-500' : ($req->status === 'approved' ? 'bg-blue-500' : 'bg-slate-500')) }}">
                        </div>

                        <div class="pl-2">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="text-white font-bold text-sm truncate pr-2">{{ $req->title }}</h4>
                                
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase
                                    {{ $req->status === 'completed' ? 'bg-green-500/10 text-green-400' : ($req->status === 'rejected' ? 'bg-red-500/10 text-red-400' : ($req->status === 'approved' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-700 text-slate-400')) }}">
                                    {{ $req->status }}
                                </span>
                            </div>
                            
                            @if($req->note)
                                <p class="text-xs text-slate-500 mb-2 line-clamp-1">"{{ $req->note }}"</p>
                            @endif

                            <div class="text-[10px] text-slate-600 flex justify-between items-center">
                                <span>{{ $req->created_at->diffForHumans() }}</span>
                                @if($req->status === 'completed')
                                    <span class="text-green-500 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Added
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($myRequests->isEmpty())
                    <div class="text-center py-10 bg-slate-900/50 rounded-xl border border-slate-800 border-dashed">
                        <div class="text-3xl mb-2 opacity-30">📪</div>
                        <p class="text-slate-500 text-xs">No requests yet.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>