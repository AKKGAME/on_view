<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StreamController extends Controller
{
    public function play($id)
    {
        // Episode ID နဲ့ Database မှာ ရှာမယ် (မရှိရင် 404 ပြမယ်)
        $episode = Episode::findOrFail($id);

        // ၁. User Login ဝင်ထားလား စစ်မယ်
        if (!Auth::check()) {
            abort(403, 'Please login first.');
        }
        
        $user = Auth::user();

        // ၂. User ဝယ်ထားပြီးသားလား စစ်မယ်
        // Livewire မှာ သိမ်းတုန်းက 'ep_' . $this->currentEpisode->id နဲ့ သိမ်းခဲ့တာဖြစ်လို့
        // ဒီမှာလည်း ID နဲ့ပဲ ပြန်စစ်ရပါမယ် (episode_number နဲ့ စစ်ရင် လွဲသွားနိုင်ပါတယ်)
        
        $hasUnlocked = Transaction::where('user_id', $user->id)
             ->where('type', 'purchase') // type ကိုပါ ထည့်စစ်တာ ပိုသေချာပါတယ်
             ->where('description', 'ep_' . $episode->id) 
             ->exists();

        // ၃. Premium ဖြစ်ပြီး မဝယ်ရသေးရင် ပိတ်မယ်
        // (Premium မဟုတ်ရင်တော့ $hasUnlocked က false ဖြစ်နေလဲ ကိစ္စမရှိပါဘူး)
        if ($episode->is_premium && !$hasUnlocked) {
             abort(403, 'Premium Content: Please unlock this episode first.');
        }

        // ၄. Video File Path ရှိမရှိ စစ်မယ် (Null Error ကာကွယ်ရန်)
        if (empty($episode->video_url)) {
            abort(404, 'Video file not configured.');
        }

        // ၅. B2/S3 Cloud Storage ကနေ Signed URL ထုတ်မယ်
        try {
            // Disk name က 'b2' မဟုတ်ဘဲ .env မှာ setup လုပ်ထားတဲ့အတိုင်း ဖြစ်ရပါမယ်
            // အများအားဖြင့် 's3' လို့ ပေးလေ့ရှိကြပါတယ် (B2 S3 compatible API သုံးရင်ပေါ့)
            $diskName = 'b2'; // သင့် config အတိုင်း 'b2' ဆိုလည်း 'b2' ထားပါ

            $temporaryUrl = Storage::disk($diskName)->temporaryUrl(
                $episode->video_url,
                now()->addMinutes(30) // ၁၀ မိနစ်/၃၀ မိနစ် လောက်ဆို လုံလောက်ပါပြီ
            );

            // Signed URL ဆီ Redirect လုပ်ပေးလိုက်မယ်
            return redirect($temporaryUrl);

        } catch (\Exception $e) {
            // Log::error($e->getMessage()); // လိုအပ်ရင် Log ထုတ်ကြည့်ပါ
            abort(404, 'Stream source unavailable.');
        }
    }
}