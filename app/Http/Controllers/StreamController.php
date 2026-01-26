<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// 🔥 BunnyStream Service ကို Import လုပ်ပါ (မလုပ်ရသေးရင် Error တက်ပါမယ်)
use App\Services\BunnyStream; 

class StreamController extends Controller
{
    /**
     * User ရဲ့ Access Control ကို စစ်ဆေးပြီးနောက် Secure Video URL ကို ပြန်ပေးခြင်း။
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
        // User က Premium Member (VIP) ဖြစ်နေရင် အကုန်ကြည့်ခွင့်ရှိသည်
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
        // Transaction Description တွင် "ep_123:" ပုံစံဖြင့် သိမ်းထားသည်ဟု ယူဆသည်
        $epIdIdentifier = 'ep_' . $episode->id . ':'; 
        
        $hasUnlocked = Transaction::where('user_id', $user->id)
             ->where('type', 'purchase') // Type ကို purchase လို့ သတ်မှတ်ထားရမယ်
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
            'error' => 'locked', // Flutter ဘက်က Dialog ပြဖို့ Key
            'coin_price' => $episode->coin_price ?? 0 // Null ဖြစ်နေရင် 0 ပြမယ်
        ], 403);
    }

    /**
     * Helper Function: Video URL ထုတ်ပေးခြင်း
     * (BunnyCDN Signed URL သို့ ပြောင်းလဲထုတ်ပေးသည်)
     */
    private function grantAccess($episode)
    {
        if (empty($episode->video_url)) {
            return response()->json(['success' => false, 'message' => 'Video source not found.'], 404);
        }

        $finalUrl = $episode->video_url;

        // 🔥 BunnyCDN Signing
        // $episode->video_url ထဲမှာ Path ပဲရှိရပါမယ် (ဥပမာ: "/onepiece/ep1.mp4")
        // Domain (http://stream.animegabar.com) မပါရပါ။
        
        if (class_exists(\App\Services\BunnyStream::class)) {
            try {
                // Database ထဲမှာ Domain ပါပြီးသားဆိုရင် ဖယ်ထုတ်ပြီး Path ပဲယူမယ်
                $path = parse_url($episode->video_url, PHP_URL_PATH); 
                
                $finalUrl = \App\Services\BunnyStream::signUrl($path, 300);
            } catch (\Exception $e) {
                // Error တက်ရင် ဘာမှ မလုပ်ဘူး
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Access granted.',
            'video_url' => $finalUrl,
            'episode_id' => $episode->id,
        ], 200);
    }
}