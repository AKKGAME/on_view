<?php

namespace App\Livewire;

use App\Models\Episode;
use App\Models\Transaction;
use App\Models\WatchHistory; // ✅ History Model ကို Import လုပ်ပါ
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WatchEpisode extends Component
{
    public $episode;
    public $anime;
    public $playlist = []; 
    public $isUnlocked = false;
    public $isInWatchlist = false; // ✅ Watchlist variable ထည့်ပါ

    public function mount(Episode $episode)
    {
        $this->episode = $episode;
        $this->anime = $episode->season->anime; 
        $this->playlist = $episode->season->episodes()->orderBy('episode_number')->get();

        if (Auth::check()) {
            // ၁. Watchlist ထဲရှိမရှိ စစ်ခြင်း
            $this->isInWatchlist = Auth::user()->watchlist()->where('anime_id', $this->anime->id)->exists();

            // ၂. Continue Watching အတွက် History မှတ်ခြင်း (အရေးကြီး)
            WatchHistory::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'anime_id' => $this->anime->id, 
                ],
                [
                    'episode_id' => $this->episode->id, // လက်ရှိကြည့်နေသော အပိုင်းကို Update လုပ်မယ်
                    'updated_at' => now(), 
                ]
            );
        }

        $this->checkUnlockStatus();
    }

    public function checkUnlockStatus()
    {
        if (!$this->episode->is_premium) {
            $this->isUnlocked = true;
            return;
        }

        if (Auth::check()) {
            $this->isUnlocked = Transaction::where('user_id', Auth::id())
                ->where('type', 'purchase')
                ->where('description', 'ep_' . $this->episode->id)
                ->exists();
        }
    }

    public function unlockEpisode()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();

        if ($this->isUnlocked) {
            $this->dispatch('notify', 
                type: 'info', 
                title: 'Already Unlocked', 
                message: 'You already own this episode.'
            );
            return;
        }

        if ($user->coins < $this->episode->coin_price) {
            $this->dispatch('notify', 
                type: 'error', 
                title: 'Insufficient Coins', 
                message: 'Please top up to unlock this episode.'
            );
            return;
        }

        $user->decrement('coins', $this->episode->coin_price);
        
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'purchase',
            'amount' => $this->episode->coin_price,
            'description' => 'ep_' . $this->episode->id,
        ]);

        $this->isUnlocked = true;

        $this->dispatch('notify', 
            type: 'success', 
            title: 'Episode Unlocked!', 
            message: "You spent {$this->episode->coin_price} coins."
        );
    }

    public function toggleWatchlist()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Auth::user()->watchlist()->toggle($this->anime->id);
        $this->isInWatchlist = !$this->isInWatchlist; // UI မှာ ချက်ချင်းပြောင်းအောင်

        $this->dispatch('notify', 
            type: 'success', 
            title: $this->isInWatchlist ? 'Added to Watchlist' : 'Removed from Watchlist',
            message: 'Your library has been updated.'
        );
    }

    public function render()
    {
        return view('livewire.watch-episode');
    }
}