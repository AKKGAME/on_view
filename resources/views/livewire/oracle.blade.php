<div class="flex flex-col h-full w-full relative overflow-hidden">
    
    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 pointer-events-none mix-blend-overlay"></div>
    <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-96 h-96 bg-blue-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div id="oracle-chat-box" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 custom-scrollbar scroll-smooth">
        
        @if(count($messages) <= 1)
            <div class="flex flex-col items-center justify-center h-full text-center opacity-60 space-y-4 pb-20">
                <div class="w-20 h-20 rounded-full bg-slate-800/50 border border-purple-500/30 flex items-center justify-center shadow-[0_0_30px_rgba(168,85,247,0.2)]">
                    <span class="text-4xl animate-pulse">🧙‍♂️</span>
                </div>
                <p class="text-slate-400 max-w-xs mx-auto text-sm">
                    "ဘယ် Anime အကြောင်းကို အရင်ဆုံး ဆွေးနွေးချင်ပါသလဲ? (ဒါမှမဟုတ်) ဘယ်လိုပုံစံ ဇာတ်ကားမျိုးကို ရှာဖွေနေပါသလဲ?"
                </p>
            </div>
        @endif

        @foreach($messages as $msg)
             <div wire:key="{{ $loop->index }}" class="flex gap-3 sm:gap-4 animate-slideIn {{ $msg['role'] === 'user' ? 'flex-row-reverse' : '' }}">
                
                <div class="shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center border border-white/10 shadow-lg overflow-hidden
                    {{ $msg['role'] === 'user' ? 'bg-slate-700' : 'bg-purple-900' }}">
                    <span class="text-sm sm:text-base">{{ $msg['role'] === 'user' ? '👤' : '🔮' }}</span>
                </div>

                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-3 sm:p-4 shadow-lg text-sm sm:text-base leading-relaxed
                     {{ $msg['role'] === 'user' 
                        ? 'bg-purple-600 text-white rounded-tr-none shadow-purple-900/20' 
                        : 'bg-slate-800/80 backdrop-blur-sm text-slate-200 border border-slate-700/50 rounded-tl-none' }}">
                    <p>{!! nl2br(e($msg['text'])) !!}</p>
                </div>
            </div>
        @endforeach

        <div wire:loading.flex wire:target="askGemini" class="gap-4 items-center p-2 opacity-70">
            <span class="text-xs text-purple-400 font-mono tracking-widest animate-pulse">ORACLE IS THINKING...</span>
        </div>
    </div>

    <div class="shrink-0 p-4 sm:p-5 bg-slate-900/80 backdrop-blur-xl border-t border-slate-800/60 z-20">
        <form wire:submit.prevent="askGemini" class="relative flex items-center gap-2">
            
            <div class="relative flex-1 group">
                <input type="text" 
                    wire:model="userInput"
                    placeholder="Ask the Oracle..." 
                    class="w-full bg-slate-950 border border-slate-700 text-white rounded-full py-3 md:py-4 pl-5 pr-12 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:shadow-[0_0_20px_rgba(168,85,247,0.2)] outline-none transition-all text-sm md:text-base placeholder:text-slate-500"
                    wire:loading.attr="disabled"
                    autocomplete="off"
                >
                
                <button type="submit" 
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-purple-600 hover:bg-purple-500 text-white rounded-full transition-all disabled:opacity-50 disabled:scale-90 transform active:scale-90 shadow-lg"
                        wire:loading.attr="disabled">
                    <svg class="w-4 h-4 md:w-5 md:h-5 transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

</div>