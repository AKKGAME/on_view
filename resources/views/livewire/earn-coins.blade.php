<div x-data="{ 
    isWatching: false, 
    canClaim: false,
    progress: 0,
    remaining: {{ $limit - $adsWatchedToday }},
    
    startVideo() {
        if (this.remaining <= 0) return;
        this.isWatching = true;
        this.canClaim = false;
        this.progress = 0;
        
        let video = this.$refs.adPlayer;
        video.currentTime = 0;
        video.play();
    },

    handleTimeUpdate() {
        let video = this.$refs.adPlayer;
        if(video.duration > 0) {
            this.progress = (video.currentTime / video.duration) * 100;
        }
    },

    handleVideoEnd() {
        this.canClaim = true;
        this.isWatching = false;
        this.$refs.adPlayer.pause();
    }
}" class="w-full">

    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 relative overflow-hidden">
        
        @if($currentAd)
            <div class="relative z-10 flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-white font-bold font-gaming text-lg flex items-center gap-2">
                        WATCH & EARN
                    </h3>
                    <p class="text-slate-400 text-sm mt-1">Watch this video to get <span class="text-yellow-400 font-bold">+{{ $currentAd->reward }} Coins</span></p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-blue-400" x-text="remaining"></div>
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Left Today</div>
                </div>
            </div>

            <div class="relative w-full aspect-video bg-black rounded-xl overflow-hidden border border-slate-700 shadow-lg mb-6">
                
                <video x-ref="adPlayer" 
                       @timeupdate="handleTimeUpdate()" 
                       @ended="handleVideoEnd()"
                       class="w-full h-full object-contain"
                       playsinline>
                    <source src="{{ $currentAd->video_path }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>

                <div x-show="!isWatching && !canClaim && remaining > 0" 
                     class="absolute inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <button @click="startVideo()" 
                            class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-full font-bold flex items-center gap-2 transition hover:scale-105">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        Watch Ad
                    </button>
                </div>

                <div x-show="isWatching" class="absolute bottom-0 left-0 w-full h-1 bg-slate-800">
                    <div class="h-full bg-blue-500 transition-all duration-100 ease-linear" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            <div>
                <button x-show="canClaim" 
                        wire:click="claimReward" 
                        @click="canClaim = false; progress = 0; remaining--"
                        class="w-full py-3 rounded-xl bg-green-600 hover:bg-green-500 text-white font-bold transition flex items-center justify-center gap-2 animate-pulse shadow-lg shadow-green-600/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Claim Reward
                </button>

                <div x-show="isWatching" class="text-center text-slate-400 text-sm font-bold animate-pulse">
                    Watching Ad... Please wait.
                </div>

                <button x-show="remaining <= 0" disabled class="w-full py-3 rounded-xl bg-slate-800 text-slate-500 font-bold cursor-not-allowed">
                    Daily Limit Reached
                </button>
            </div>

        @else
            <div class="text-center py-10">
                <div class="text-4xl mb-2">📺</div>
                <h4 class="text-white font-bold">No Ads Available</h4>
                <p class="text-slate-500 text-sm">Check back later for new tasks.</p>
            </div>
        @endif

    </div>
</div>