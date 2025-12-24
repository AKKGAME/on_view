<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedeemController extends Controller
{
    public function redeem(Request $request)
    {
        // ၁. Input စစ်ဆေးခြင်း
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user(); // လက်ရှိ User
        $code = strtoupper($request->code);

        // ၂. ကူပွန်ရှာခြင်း
        $coupon = CoinCoupon::where('code', $code)->first();

        // --- Validation အဆင့်ဆင့် ---

        // ကူပွန် မရှိရင်
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 404);
        }

        // ကူပွန် ပိတ်ထားရင် သို့မဟုတ် သက်တမ်းကုန်နေရင်
        if (!$coupon->is_active || ($coupon->expires_at && now()->gt($coupon->expires_at))) {
            return response()->json(['success' => false, 'message' => 'This coupon has expired.'], 400);
        }

        // လူဦးရေ ပြည့်သွားရင်
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'This coupon usage limit has been reached.'], 400);
        }

        // ဒီ User သုံးပြီးသားလား စစ်မယ် (Pivot Table Check)
        // Note: coin_coupon_user table မရှိသေးရင် error တက်ပါမယ် (php artisan migrate လုပ်ပါ)
        $alreadyUsed = DB::table('coin_coupon_user')
            ->where('user_id', $user->id)
            ->where('coin_coupon_id', $coupon->id)
            ->exists();

        if ($alreadyUsed) {
            return response()->json(['success' => false, 'message' => 'You have already redeemed this coupon.'], 400);
        }

        // ၃. Coin ထည့်ပေးခြင်း (Transaction)
        try {
            DB::transaction(function () use ($user, $coupon) {
                // ✅ ပြင်ဆင်ထားသည်: 'balance' အစား 'coins' ကိုသုံးပါ (သင့် User table ပေါ်မူတည်သည်)
                $user->increment('coins', $coupon->coin_amount);

                // Coupon သုံးတဲ့စာရင်းထဲ User ကိုထည့်မယ်
                $coupon->users()->attach($user->id, ['redeemed_at' => now()]);

                // Coupon ရဲ့ used_count ကို ၁ တိုးမယ်
                $coupon->increment('used_count');
            });

            return response()->json([
                'success' => true,
                'message' => "Success! You received {$coupon->coin_amount} coins.",
                // ✅ ပြင်ဆင်ထားသည်: coins ကိုပဲ ပြန်ပို့မယ်
                'new_balance' => $user->fresh()->coins,
            ], 200);

        } catch (\Exception $e) {
            // ✅ ပြင်ဆင်ထားသည်: Error အစစ်ကို ထုတ်ပြစေမယ် (Debugging အတွက်)
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHistory(Request $request)
    {
        $user = auth()->user();

        // Pivot table (coin_coupon_user) နဲ့ join ပြီး ယူမယ်
        $history = $user->redeemedCoupons()
                        ->orderByPivot('redeemed_at', 'desc') // နောက်ဆုံးသုံးတာ အရင်ပြမယ်
                        ->limit(10) // ၁၀ ခုပဲ ယူမယ်
                        ->get()
                        ->map(function ($coupon) {
                            return [
                                'code' => $coupon->code,
                                'amount' => $coupon->coin_amount,
                                'redeemed_at' => \Carbon\Carbon::parse($coupon->pivot->redeemed_at)->diffForHumans(), // "2 mins ago" ပုံစံပြမယ်
                            ];
                        });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}