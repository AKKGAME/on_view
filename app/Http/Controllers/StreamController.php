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

        // 2. User Login ဝင်ထားလား စစ်ဆေးခြင်း
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login first.'], 401);
        }
        
        $user = Auth::user();

        // 🟢 3. VIP CHECK (Subscription First)
        // User က Premium Member ဖြစ်နေရင် ဝယ်ထားလား ဆက်မစစ်ဘဲ တန်းပေးကြည့်မယ်
        if ($user->is_premium) {
            return $this->grantAccess($episode);
        }

        // 🟢 4. FREE CONTENT CHECK
        // Episode က Premium မဟုတ်ရင် (Free ဆိုရင်) ပေးကြည့်မယ်
        if (!$episode->is_premium) {
            return $this->grantAccess($episode);
        }

        // 🟢 5. PURCHASED CHECK (Individual Buy)
        // User က ဒီ Episode ကို သီးသန့်ဝယ်ထားပြီးသားလား စစ်ဆေးခြင်း
        // Format: "ep_ID:TITLE..."
        $epIdIdentifier = 'ep_' . $episode->id . ':'; 
        
        $hasUnlocked = Transaction::where('user_id', $user->id)
             ->where('type', 'purchase') 
             ->where('description', 'like', $epIdIdentifier . '%') 
             ->exists();

        if ($hasUnlocked) {
             return $this->grantAccess($episode);
        }

        // 🔴 6. ACCESS DENIED (Lock)
        // Premium ဖြစ်ပြီး၊ VIP လည်းမဟုတ်၊ ဝယ်လည်းမဝယ်ရသေးရင် ပိတ်မယ်
        return response()->json([
            'success' => false,
            'message' => 'Premium Content: Please unlock this episode to stream.',
            'error' => 'locked', // Flutter ဘက်က Dialog ပြဖို့
            'coin_price' => $episode->coin_price ?? 0 // ဈေးနှုန်းထည့်ပေးလိုက်သည်
        ], 403);
    }

    /**
     * Helper Function: Video URL ထုတ်ပေးခြင်း
     */
    private function grantAccess($episode)
    {
        // Video File Path/URL ရှိမရှိ စစ်ဆေးခြင်း
        if (empty($episode->video_url)) {
            return response()->json(['success' => false, 'message' => 'Video URL not configured.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Access granted.',
            'video_url' => $episode->video_url, 
            'episode_id' => $episode->id,
        ], 200);
    }
}