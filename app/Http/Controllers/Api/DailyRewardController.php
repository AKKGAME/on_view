<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyRewardController extends Controller
{
    public function status()
    {
        $user = Auth::user();
        $streak = $user->check_in_streak ?? 0;
        $alreadyClaimed = false;

        if ($user->last_check_in && Carbon::parse($user->last_check_in)->isToday()) {
            $alreadyClaimed = true;
        }

        // Status ပြတဲ့အခါ Claim ပြီးရင် Streak က 7 ဖြစ်နေမယ်
        // နောက်ရက်ကျရင် 1 ဖြစ်မယ်ဆိုတာ UI က Auto သိဖို့လိုပါမယ်
        // ဒါပေမယ့် Logic အရ ဒီအတိုင်းထားတာ အဆင်ပြေပါတယ်

        $nextStreak = $alreadyClaimed ? $streak : $streak + 1;
        
        // ၇ ရက်ပြည့်ပြီးရင် ၁ ရက်ပြန်စမှာမို့ 8 လို့မပြဘဲ Calculation ဝင်မယ်
        if ($nextStreak > 7) $nextStreak = 1; 

        $rewardAmount = ($nextStreak == 7) ? 200 : 20;

        return response()->json([
            'streak' => $streak,
            'already_claimed' => $alreadyClaimed,
            'reward_amount' => $rewardAmount,
        ]);
    }

    public function claim()
    {
        $user = Auth::user();

        // ၁. ဒီနေ့ ယူပြီးသားလား စစ်မယ်
        if ($user->last_check_in && Carbon::parse($user->last_check_in)->isToday()) {
            return response()->json(['message' => 'Already claimed today'], 400);
        }

        // ၂. Streak တွက်ချက်ခြင်း Logic (အဓိက ပြင်ဆင်ထားသည့်နေရာ)
        if ($user->last_check_in && !Carbon::parse($user->last_check_in)->isYesterday()) {
            // (က) မနေ့က မဝင်ခဲ့ရင် (ရက်ကျော်သွားရင်) -> Reset to 1
            $user->check_in_streak = 1;
        } else {
            // (ခ) မနေ့က ဝင်ခဲ့တယ်၊ ဒါပေမယ့် မနေ့က ၇ ရက်မြောက်ဖြစ်နေရင် -> Reset to 1
            if ($user->check_in_streak >= 7) {
                $user->check_in_streak = 1;
            } else {
                // (ဂ) ရိုးရိုးရက်တွေဆိုရင် -> +1 ပေါင်းမယ်
                $user->check_in_streak += 1;
            }
        }

        // ၃. Reward တွက်ချက်ခြင်း
        // ၇ ရက်မြောက်နေ့ဆိုရင် 200၊ ကျန်ရ်ဆို 20
        $amount = ($user->check_in_streak == 7) ? 200 : 20;

        $user->last_check_in = Carbon::today();
        $user->increment('coins', $amount);
        $user->increment('xp', 20);
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'reward',
            'amount' => $amount,
            'description' => 'Daily Check-in Day ' . $user->check_in_streak,
        ]);

        return response()->json([
            'success' => true,
            'message' => "+{$amount} Coins added!",
            'new_balance' => $user->coins,
            'new_streak' => $user->check_in_streak
        ]);
    }
}