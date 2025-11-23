<?php

namespace App\Livewire;

use App\Models\Anime;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowAnime extends Component
{
    public $anime;
    public $currentSeason;
    public $episodes = [];
    public $isInWatchlist = false;

    public function mount($slug)
    {
        // Anime နဲ့ Seasons ကို ဆွဲထုတ်မယ်
        $this->anime = Anime::where('slug', $slug)->with('seasons')->firstOrFail();
        
        // ပထမဆုံး Season ကို Default ရွေးမယ်
        $this->currentSeason = $this->anime->seasons->first();

        if ($this->currentSeason) {
            $this->loadEpisodes();
        }

        // Watchlist ထဲရှိမရှိ စစ်မယ်
        if (Auth::check()) {
            $this->isInWatchlist = Auth::user()->watchlist()->where('anime_id', $this->anime->id)->exists();
        }
    }

    public function selectSeason($seasonId)
    {
        $this->currentSeason = $this->anime->seasons->find($seasonId);
        $this->loadEpisodes();
    }

    public function loadEpisodes()
    {
        $this->episodes = $this->currentSeason->episodes()->orderBy('episode_number')->get();
    }

    public function toggleWatchlist()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Auth::user()->watchlist()->toggle($this->anime->id);
        $this->isInWatchlist = !$this->isInWatchlist;

        $this->dispatch('notify', 
            type: 'success', 
            title: $this->isInWatchlist ? 'Added to Watchlist' : 'Removed from Watchlist',
            message: 'Your library has been updated.'
        );
    }

    public function render()
    {
        // Episode List မှာ Premium/Free ခွဲပြဖို့အတွက် ဝယ်ပြီးသား ID တွေကို ယူမယ်
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