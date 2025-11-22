<?php

namespace App\Livewire;

use App\Models\CustomAd;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EarnCoins extends Component
{
    public $limit = 5;
    public $adsWatchedToday = 0;
    public $currentAd;

    public function mount()
    {
        $this->checkDailyLimit();
        $this->loadRandomAd();
    }

    public function loadRandomAd()
    {
        $this->currentAd = CustomAd::where('is_active', true)->inRandomOrder()->first();
    }

    public function checkDailyLimit()
    {
        $this->adsWatchedToday = Transaction::where('user_id', Auth::id())
            ->where('type', 'ad_reward')
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    public function claimReward()
    {
        $this->checkDailyLimit();

        if (!$this->currentAd) {
            return;
        }

        // Daily Limit ပြည့်သွားရင် Error Alert ပြမယ်
        if ($this->adsWatchedToday >= $this->limit) {
            $this->dispatch('notify', 
                type: 'error',
                title: 'Daily Limit Reached',
                message: 'You have reached the daily limit of 5 ads.'
            );
            return;
        }

        $reward = $this->currentAd->reward;

        $user = Auth::user();
        $user->increment('coins', $reward);
        $user->increment('xp', 5);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'ad_reward',
            'amount' => $reward,
            'description' => 'Watched Ad: ' . $this->currentAd->title,
        ]);

        $this->adsWatchedToday++;
        $this->loadRandomAd();

        // Success Alert (Double Quotes သုံးထားပါတယ်)
        $this->dispatch('notify', 
            type: 'success', 
            title: 'Reward Claimed!', 
            message: "+{$reward} Coins added to your wallet."
        );
            
        $this->dispatch('coin-updated');
    }

    public function render()
    {
        return view('livewire.earn-coins');
    }
}