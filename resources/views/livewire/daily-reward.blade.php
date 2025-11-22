<div class="p-4 bg-slate-900 rounded-xl border border-slate-800 shadow-2xl relative overflow-hidden">
    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-32 h-32 rounded-full bg-purple-500/20 blur-3xl"></div>

    <div class="relative z-10 text-center">
        <h3 class="text-white font-bold font-gaming text-lg mb-1">DAILY LOGIN BONUS</h3>
        <p class="text-slate-400 text-xs mb-4">Check in daily to earn free coins!</p>

        <div class="flex justify-center gap-2 mb-6">
            @for ($i = 1; $i <= 7; $i++)
                @php
                    // လက်ရှိ Streak ထက် နည်းနေရင် (ပြီးခဲ့ပြီ) -> Green
                    // ဒီနေ့အတွက်ဆိုရင် (Active) -> Purple Ring
                    // နောက်လာမယ့်ရက် -> Gray
                    $currentDay = ($streak % 7 == 0 && $streak > 0) ? 7 : ($streak % 7);
                    $isActive = ($i == $currentDay + 1 && !$alreadyClaimed) || ($i == $currentDay && $alreadyClaimed);
                    $isPast = $i <= $currentDay;
                @endphp

                <div class="flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all
                        {{ $isPast ? 'bg-green-500/20 border-green-500 text-green-400' : 
                           ($isActive ? 'bg-purple-600 border-purple-400 text-white shadow-[0_0_10px_rgba(168,85,247,0.5)] scale-110' : 'bg-slate-800 border-slate-700 text-slate-600') }}">
                        @if($i == 7) 🎁 @else {{ $i }} @endif
                    </div>
                </div>
            @endfor
        </div>

        <div class="bg-slate-950 rounded-lg p-3 mb-4 border border-slate-800">
            <div class="text-slate-500 text-xs uppercase font-bold">Today's Reward</div>
            <div class="text-2xl font-bold text-yellow-400 flex items-center justify-center gap-2">
                <span>+{{ $rewardAmount }}</span>
                <span class="text-sm text-yellow-600">Coins</span>
            </div>
        </div>

        @if($alreadyClaimed)
            <button disabled class="w-full py-2 rounded-lg bg-slate-800 text-slate-500 font-bold cursor-not-allowed">
                Come Back Tomorrow
            </button>
            <p class="text-[10px] text-slate-500 mt-2">Streak: {{ $streak }} Days</p>
        @else
            <button wire:click="claim" class="w-full py-2 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold hover:scale-105 transition shadow-lg shadow-purple-600/30">
                CLAIM REWARD
            </button>
        @endif
    </div>
</div>