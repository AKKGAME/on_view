<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use App\Models\SystemSetting;
use App\Models\Transaction;

class Register extends Component
{
    public $name;
    public $phone;
    public $password;
    public $password_confirmation;
    public $referral_code;

public function register()
{
    $this->validate([
        'name' => 'required|min:3',
        'phone' => 'required|numeric|unique:users,phone',
        'password' => 'required|min:6|confirmed',
        // Referral code က ရှိချင်မှရှိမယ်၊ ရှိရင် users table မှာ ရှိရမယ်
        'referral_code' => 'nullable|exists:users,referral_code', 
    ]);

    // Admin သတ်မှတ်ထားတဲ့ Coin တွေကို ဆွဲထုတ်မယ်
    $referrerBonus = (int) SystemSetting::where('key', 'referral_bonus_referrer')->value('value') ?? 200;
    $newPlayerBonus = (int) SystemSetting::where('key', 'referral_bonus_referee')->value('value') ?? 100;

    // မိတ်ဆက်သူကို ရှာမယ်
    $referrer = null;
    if ($this->referral_code) {
        $referrer = User::where('referral_code', $this->referral_code)->first();
    }

    // User အသစ်ဖန်တီးခြင်း
    $user = User::create([
        'name' => $this->name,
        'phone' => $this->phone,
        'password' => Hash::make($this->password),
        'rank' => 'Newbie',
        'coins' => $referrer ? $newPlayerBonus : 0, // Referral ပါရင် Bonus ရမယ်
        'xp' => 0,
        'referrer_id' => $referrer ? $referrer->id : null,
    ]);

    // Referral Logic (Coin ပေးခြင်း & Transaction မှတ်ခြင်း)
    if ($referrer) {
        // ၁. လူဟောင်းကို Coin ပေးမယ်
        $referrer->increment('coins', $referrerBonus);
        Transaction::create([
            'user_id' => $referrer->id,
            'type' => 'referral_bonus',
            'amount' => $referrerBonus,
            'description' => 'Invited friend: ' . $user->name,
        ]);

        // ၂. လူသစ်ကို Transaction မှတ်ပေးမယ် (Coin က create မှာ ထည့်ပြီးပြီ)
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'referral_bonus',
            'amount' => $newPlayerBonus,
            'description' => 'Invited by: ' . $referrer->name,
        ]);
    }

    Auth::login($user);
    return redirect()->route('home');
}
}