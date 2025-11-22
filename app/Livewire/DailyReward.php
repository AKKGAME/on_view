<?php

namespace App\Livewire;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Filament\Notifications\Notification;

class DailyReward extends Component
{
    public $alreadyClaimed = false;
    public $streak = 0;
    public $rewardAmount = 0;

    public function mount()
    {
        $user = Auth::user();
        $this->streak = $user->check_in_streak;
        
        // Carbon date casting ရှိမှ isToday() သုံးလို့ရမယ်
        if ($user->last_check_in && $user->last_check_in->isToday()) {
            $this->alreadyClaimed = true;
        }

        $nextStreak = $this->alreadyClaimed ? $this->streak : $this->streak + 1;
        $this->rewardAmount = ($nextStreak % 7 == 0) ? 500 : 50;
    }

    public function claim()
    {
        $user = Auth::user();

        // တကယ်လို့ ဒီနေ့ ယူပြီးသားဆိုရင် ဘာမှဆက်မလုပ်နဲ့ (Double Check)
        if ($user->last_check_in && $user->last_check_in->isToday()) {
            $this->alreadyClaimed = true;
            return;
        }

        // မနေ့က မဟုတ်ရင် (ရက်ကျော်သွားရင်) Streak ပြန်စမယ်
        if ($user->last_check_in && !$user->last_check_in->isYesterday()) {
            $user->check_in_streak = 1;
        } else {
            $user->check_in_streak += 1;
        }

        $amount = ($user->check_in_streak % 7 == 0) ? 500 : 50;

        $user->last_check_in = Carbon::today(); // ဒီနေ့ရက်စွဲသွင်းမယ်
        $user->increment('coins', $amount);
        $user->increment('xp', 20);
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'topup',
            'amount' => $amount,
            'description' => 'Daily Check-in Day ' . $user->check_in_streak,
        ]);

        $this->alreadyClaimed = true;
        
        // Notification::make()
        //     ->title('Daily Reward Claimed!')
        //     ->body("+{$amount} Coins added.")
        //     ->success()
        //     ->send();
            
        // return redirect(request()->header('Referer'));

$this->dispatch('notify', 
    type: 'success', 
    title: 'Daily Reward Claimed!', 
    message: "+{$amount} Coins added to your wallet."
);
    }

    public function render()
    {
        return view('livewire.daily-reward');
    }
}