<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Transaction စီမံခန့်ခွဲရန် DB Facade ကိုသုံးသည်

class TransactionController extends Controller
{
    /**
     * Episode တစ်ခုကို ဝယ်ယူ (Unlock) ရန်။
     *
     * POST /purchase/episode/{episode}
     *
     * @param  \App\Models\Episode  $episode
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseEpisode(Request $request, Episode $episode)
    {
        // 1. စစ်ဆေးမှုများ
        $user = $request->user();
        $cost = $episode->price; // Episode ရဲ့ စျေးနှုန်းကို ယူပါမည်

        if ($cost <= 0) {
            // စျေးနှုန်း 0 ဖြစ်ရင်တောင် ဝယ်ယူမှုမှတ်တမ်း ထားခဲ့နိုင်ပေမယ့် စျေးနှုန်း 0 နဲ့ပဲ ပေးပါ
            $cost = 0; 
        }

        if ($user->coins < $cost) {
            return response()->json(['message' => 'Insufficient coins.'], 403);
        }

        // ဝယ်ယူပြီးသားလား ပြန်စစ်ခြင်း
        $alreadyUnlocked = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->where('description', 'ep_' . $episode->id)
            ->exists();
            
        if ($alreadyUnlocked) {
            return response()->json(['message' => 'This episode is already unlocked.'], 200);
        }

        // 2. Database Transaction စတင်ခြင်း (ဒင်္ဂါးနုတ်ခြင်းနှင့် မှတ်တမ်းတင်ခြင်း)
        try {
            DB::beginTransaction();

            // a. User ရဲ့ ဒင်္ဂါးနုတ်ယူခြင်း
            $user->decrement('coins', $cost);

            // b. ဝယ်ယူမှုမှတ်တမ်း (Transaction) ဖန်တီးခြင်း
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => $cost,
                'description' => 'ep_' . $episode->id, // Episode ID ကို သိမ်းဆည်းထားသည်
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Episode unlocked successfully!',
                'new_coins' => $user->coins - $cost // update ဖြစ်ပြီးသား coins
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            // Error handling ပိုကောင်းအောင်လုပ်ပါ
            return response()->json(['message' => 'Transaction failed. Please try again later.'], 500);
        }
    }
}