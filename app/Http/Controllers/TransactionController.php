<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Notifications\SystemNotification; // ✅ Notification Class ကို Import လုပ်ပါ

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
        // 1. Episode ကနေ Anime Title ကို Eager Loading ဖြင့် ရယူခြင်း
        $episode->load('season.anime'); 
        
        $user = $request->user();
        $cost = $episode->coin_price; 

        // Title ယူခြင်း
        $animeTitle = $episode->season->anime->title ?? 'Unknown Anime';
        $episodeNumber = $episode->episode_number;
        $episodeId = $episode->id;
        
        // 2. စစ်ဆေးမှုများ
        if ($cost <= 0) {
            $cost = 0; 
        }

        if ($user->coins < $cost) {
            return response()->json(['message' => 'Insufficient coins.'], 403);
        }

        // 3. ဝယ်ယူပြီးသားလား ပြန်စစ်ခြင်း
        $epIdIdentifier = 'ep_' . $episodeId . ':'; 

        $alreadyUnlocked = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->where('description', 'like', $epIdIdentifier . '%')
            ->exists();
            
        if ($alreadyUnlocked) {
            return response()->json(['message' => 'This episode is already unlocked.'], 200);
        }

        // 4. Database Transaction စတင်ခြင်း (ဒင်္ဂါးနုတ်ခြင်းနှင့် မှတ်တမ်းတင်ခြင်း)
        try {
            DB::beginTransaction();

            // a. User ရဲ့ ဒင်္ဂါးနုတ်ယူခြင်း
            $user->decrement('coins', $cost);

            // b. ဝယ်ယူမှုမှတ်တမ်း (Transaction) ဖန်တီးခြင်း
            $description = 'ep_' . $episodeId . ':' . $animeTitle . ' - Ep ' . $episodeNumber;

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => $cost,
                'description' => $description,
            ]);
            
            // 5. ✅ NEW: Notification ပို့ခြင်း (Transaction အောင်မြင်ပါက)
            $user->notify(
                new SystemNotification(
                    "Episode Unlocked: {$animeTitle}", 
                    "You successfully unlocked Ep {$episodeNumber} of {$animeTitle} by spending {$cost} coins.", 
                    'success' 
                )
            );

            DB::commit();

            return response()->json([
                'message' => 'Episode unlocked successfully!',
                'new_coins' => $user->coins // update ဖြစ်ပြီးသား coins
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // 💡 Optional: Failed Transaction အတွက် Notification ပို့နိုင်သည်
            // $user->notify(new SystemNotification('Purchase Failed', 'An error occurred during transaction.', 'error'));
            
            return response()->json(['message' => 'Transaction failed. Please try again later.'], 500);
        }
    }
}