<?php

namespace App\Livewire;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Transaction;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowAnime extends Component
{
    public $anime;
    public $currentSeason;
    public $currentEpisode = null;
    public $episodes = [];
    
    public $isUnlocked = false; 
    public $isInWatchlist = false;
    public $showPlayer = false;

    public function mount($slug)
    {
        $this->anime = Anime::where('slug', $slug)->with('seasons')->firstOrFail();
        $this->currentSeason = $this->anime->seasons->first();

        if ($this->currentSeason) {
            $this->episodes = $this->currentSeason->episodes()->orderBy('episode_number')->get();
        }

        if (Auth::check()) {
            $this->isInWatchlist = Auth::user()->watchlist()->where('anime_id', $this->anime->id)->exists();
        }
    }

    public function selectSeason($seasonId)
    {
        $this->currentSeason = $this->anime->seasons->find($seasonId);
        $this->currentEpisode = null; 
        $this->loadEpisodes();
    }

    public function loadEpisodes()
    {
        $this->episodes = $this->currentSeason->episodes()->orderBy('episode_number')->get();
    }

    public function selectEpisode($episodeId)
    {
        $this->currentEpisode = Episode::find($episodeId);
        
        // ကြည့်လက်စ မှတ်တမ်း (History)
        if (Auth::check()) {
            WatchHistory::updateOrCreate(
                ['user_id' => Auth::id(), 'episode_id' => $episodeId],
                ['updated_at' => now()]
            );
        }

        $this->checkUnlockStatus();
        $this->showPlayer = true;
    }

    // Player ပိတ်တဲ့အခါ ခေါ်ဖို့ (Optional)
    public function closePlayer()
    {
        $this->showPlayer = false;
        $this->currentEpisode = null;
    }

    public function checkUnlockStatus()
    {
        if (!$this->currentEpisode->is_premium) {
            $this->isUnlocked = true;
            return;
        }

        if (!Auth::check()) {
            $this->isUnlocked = false;
            return;
        }

        $hasBought = Transaction::where('user_id', Auth::id())
            ->where('type', 'purchase')
            ->where('description', 'ep_' . $this->currentEpisode->id)
            ->exists();

        $this->isUnlocked = $hasBought;
    }

    public function unlockEpisode()
    {
        if (!Auth::check()) {
            return redirect()->route('login'); // Route name ကို သင့် project အတိုင်း ပြင်ပါ
        }

        $user = Auth::user();
        
        // ဝယ်ပြီးသား ဟုတ်မဟုတ် စစ်မယ်
        $alreadyBought = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->where('description', 'ep_' . $this->currentEpisode->id)
            ->exists();

        if ($alreadyBought) {
            $this->isUnlocked = true;
            $this->dispatch('notify', type: 'info', title: 'Info', message: 'You already own this episode.');
            return;
        }

        $price = $this->currentEpisode->coin_price;

        // Coin လောက်မလောက် စစ်မယ်
        if ($user->coins < $price) {
            $this->dispatch('notify', type: 'error', title: 'Error', message: 'Insufficient Coins!');
            return;
        }

        // Coin ဖြတ်မယ်
        $user->decrement('coins', $price);
        $user->increment('xp', 10);

        // Transaction မှတ်မယ်
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'purchase',
            'amount' => $price,
            'description' => 'ep_' . $this->currentEpisode->id,
        ]);

        $this->isUnlocked = true;

        $this->dispatch('notify', type: 'success', title: 'Success', message: "Episode Unlocked!");
    }

    public function toggleWatchlist()
    {
        if (!Auth::check()) return;

        Auth::user()->watchlist()->toggle($this->anime->id);
        $this->isInWatchlist = !$this->isInWatchlist;

        $this->dispatch('notify', 
            type: 'success', 
            title: $this->isInWatchlist ? 'Added' : 'Removed',
            message: 'Watchlist updated.'
        );
    }

    public function render()
    {
        // Episode List မှာ သော့ခလောက်ပုံ ပြ/မပြ သိဖို့ ဝယ်ပြီးသား List ကို ဒီမှာ ဆွဲထုတ်ပါမယ်
        $unlockedEpisodeIds = [];
        
        if (Auth::check()) {
            $unlockedEpisodeIds = Transaction::where('user_id', Auth::id())
                ->where('type', 'purchase')
                ->where('description', 'like', 'ep_%')
                ->pluck('description')
                ->map(fn($desc) => (int) str_replace('ep_', '', $desc))
                ->toArray();
        }

        return view('livewire.show-anime', [
            'unlockedEpisodeIds' => $unlockedEpisodeIds
        ]);
    }
}