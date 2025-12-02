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
        
        // ဝယ်ယူထားသည့် episode IDs များကို ထုတ်ယူခြင်း
        $purchasedEpisodeIds = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            // 'ep_123' ပုံစံမျိုးကို စစ်ဆေးခြင်း
            ->where('description', 'like', 'ep\_%') 
            ->pluck('description')
            ->map(fn ($desc) => str_replace('ep_', '', $desc))
            ->unique()
            ->toArray();

        $libraryAnimes = Anime::whereHas('seasons.episodes', function ($query) use ($purchasedEpisodeIds) {
            $query->whereIn('id', $purchasedEpisodeIds);
        })
        ->with([
            'seasons.episodes' => function ($query) use ($purchasedEpisodeIds) {
                $query->whereIn('id', $purchasedEpisodeIds);
            }
        ])
        ->distinct()
        ->get();

        return response()->json($libraryAnimes);
    }

    // GET /user/topup-history
    public function getTopupHistory(Request $request)
    {
        $history = PaymentRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($history);
    }
}