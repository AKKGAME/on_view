<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Anime;
use App\Models\PaymentRequest;

class UserController extends Controller
{
    // GET /user (User Profile)
    public function getProfile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user, 
            'coins' => $user->coins, 
            'xp' => $user->xp, 
            'rank' => $user->rank,
            'referral_code' => $user->referral_code,
        ]);
    }

    // GET /user/library (Unlocked Animes)
    public function getLibrary(Request $request)
    {
        $user = $request->user();
        
        // 1. Transaction များကို အရင်ဆွဲထုတ်မည် (SQL Filtering အစား PHP Collection ဖြင့်စစ်မည်)
        $transactions = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase') // Type ကို သေချာစစ်ပါ (Database မှာ 'purchase' ဟုတ်မဟုတ်)
            ->get();

        // 2. ID များကို သန့်စင်ပြီး ယူမည်
        $purchasedEpisodeIds = $transactions->map(function ($transaction) {
            
            // A. အကယ်၍ episode_id column ရှိရင် အရင်ယူမယ် (အကောင်းဆုံး)
            if (!empty($transaction->episode_id)) {
                return (int) $transaction->episode_id;
            }

            // B. Description ကနေ "ep_123" ကိုဖြတ်ယူမယ်
            $desc = $transaction->description; 
            // "ep_" နဲ့စရင် ဖြတ်ယူမယ်
            if (str_starts_with($desc, 'ep_')) {
                return (int) str_replace('ep_', '', $desc);
            }

            return null; // ID မဟုတ်ရင် ကျော်မယ်
        })
        ->filter() // null များကို ဖယ်မည်
        ->unique()
        ->values()
        ->toArray();

        // Debug လုပ်ချင်ရင် ဒီလိုင်းကိုဖွင့်ပြီး ID တွေထွက်လာလား စစ်ကြည့်ပါ
        // return response()->json(['debug_ids' => $purchasedEpisodeIds]);

        // ID မရှိရင် Empty array ပြန်ပို့မည်
        if (empty($purchasedEpisodeIds)) {
            return response()->json(['data' => []]);
        }

        // 3. Anime နှင့် Episode များကို Relationship ဖြင့် ဆွဲထုတ်မည်
        $libraryAnimes = Anime::whereHas('seasons.episodes', function ($query) use ($purchasedEpisodeIds) {
            $query->whereIn('id', $purchasedEpisodeIds);
        })
        ->with([
            // Season ကိုပါ Filter လုပ်ပေးခြင်း (Performance ပိုကောင်းစေသည်)
            'seasons' => function ($query) use ($purchasedEpisodeIds) {
                $query->whereHas('episodes', function ($q) use ($purchasedEpisodeIds) {
                    $q->whereIn('id', $purchasedEpisodeIds);
                });
            },
            // Episode များကို Filter လုပ်ခြင်း (အရေးကြီးဆုံးအပိုင်း)
            'seasons.episodes' => function ($query) use ($purchasedEpisodeIds) {
                $query->whereIn('id', $purchasedEpisodeIds);
            }
        ])
        ->distinct()
        ->get();

        // Flutter ဘက်က format အတိုင်း return ပြန်မည်
        return response()->json(['data' => $libraryAnimes]);
    }

    // GET /user/topup-history
    public function getTopupHistory(Request $request)
    {
        $history = PaymentRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($history);
    }
    
    // GET /user/transactions
    public function getTransactions(Request $request)
    {
        return \App\Models\Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }
}