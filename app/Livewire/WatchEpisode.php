<?php

namespace App\Livewire;

use App\Models\Episode;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WatchEpisode extends Component
{
    public $episode;
    public $anime;
    public $playlist = []; 
    public $isUnlocked = false;

    public function mount(Episode $episode)
    {
        $this->episode = $episode;
        $this->anime = $episode->season->anime; 
        $this->playlist = $episode->season->episodes()->orderBy('episode_number')->get();
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

        // (Safety Check) ဝယ်ပြီးသား ဖြစ်နေရင် Coin ထပ်မဖြတ်တော့ဘဲ Success ပြမယ်
        if ($this->isUnlocked) {
            $this->dispatch('notify', 
                type: 'info', 
                title: 'Already Unlocked', 
                message: 'You already own this episode.'
            );
            return;
        }

        // Coin မလောက်ရင် Error ပြမယ်
        if ($user->coins < $this->episode->coin_price) {
            $this->dispatch('notify', 
                type: 'error', 
                title: 'Insufficient Coins', 
                message: 'Please top up to unlock this episode.'
            );
            return;
        }

        // Coin ဖြတ်မယ်
        $user->decrement('coins', $this->episode->coin_price);
        
        // Transaction မှတ်မယ်
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'purchase',
            'amount' => $this->episode->coin_price,
            'description' => 'ep_' . $this->episode->id,
        ]);

        $this->isUnlocked = true;

        // ✅ ဝယ်ပြီးကြောင်း Notification ပြမည့် ကုဒ်
        $this->dispatch('notify', 
            type: 'success', 
            title: 'Episode Unlocked!', 
            message: "You spent {$this->episode->coin_price} coins to watch this episode."
        );
    }

    public function render()
    {
        return view('livewire.watch-episode');
    }
}