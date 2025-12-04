<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamController extends Controller
{
    /**
     * User ရဲ့ Access Control ကို စစ်ဆေးပြီးနောက် Video URL ကို ပြန်ပေးခြင်း။
     *
     * @param int $id Episode ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function play($id)
    {
        // 1. Episode Model ကိုရှာဖွေခြင်း
        $episode = Episode::findOrFail($id);

        // ၂. User Login ဝင်ထားလား စစ်ဆေးခြင်း
        if (!Auth::check()) {
            return response()->json(['message' => 'Please login first.'], 401);
        }
        
        $user = Auth::user();

        // ၃. User ဝယ်ထားပြီးသားလား စစ်ဆေးခြင်း
        // 💡 FIX: description format အသစ် (ep_ID:TITLE...) ကို စစ်ဆေးရန် LIKE ကို သုံးသည်
        $epIdIdentifier = 'ep_' . $episode->id . ':'; 
        
        $hasUnlocked = Transaction::where('user_id', $user->id)
             ->where('type', 'purchase') 
             // description က ep_ID: နဲ့ စတာကို စစ်ဆေးသည်
             ->where('description', 'like', $epIdIdentifier . '%') 
             ->exists();

        // ၄. Premium ဖြစ်ပြီး မဝယ်ရသေးရင် ပိတ်ခြင်း
        if ($episode->is_premium && !$hasUnlocked) {
             // 💡 FIX: Message ကို ပိုရှင်းလင်းစေရန်
             return response()->json(['message' => 'Premium Content: Please unlock this episode to stream.'], 403);
        }

        // ၅. Video File Path/URL ရှိမရှိ စစ်ဆေးခြင်း
        if (empty($episode->video_url)) {
            return response()->json(['message' => 'Video URL not configured.'], 404);
        }

        // ----------------------------------------------------
        // ✅ Access ရရှိပါက၊ URL အပြည့်အစုံကို Client သို့ ပြန်ပေးခြင်း
        // ----------------------------------------------------
        
        return response()->json([
            'message' => 'Access granted.',
            'video_url' => $episode->video_url, 
            'episode_id' => $episode->id,
        ], 200);
    }
}