<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    // app/Http/Controllers/Api/AdController.php

public function getRandomAd()
{
    $ad = CustomAd::where('is_active', true)->inRandomOrder()->first(); 

    if (!$ad) {
        return response()->json([
            'success' => false,
            'message' => 'No ads available (Database Empty?)'
        ], 404);
    }

    $ad->video_url = $ad->video_path; 

    return response()->json([
        'success' => true,
        'data' => $ad
    ]);
}

    public function rewardXp(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:custom_ads,id',
        ]);

        $user = Auth::user();
        $ad = CustomAd::find($request->ad_id);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->is_vip) {
            return response()->json(['message' => 'VIP users do not need rewards'], 200);
        }

        $user->increment('xp', $ad->reward);

        return response()->json([
            'success' => true,
            'message' => "You earned {$ad->reward} XP!",
            'current_xp' => $user->xp
        ]);
    }
}