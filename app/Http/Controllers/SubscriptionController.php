<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\Transaction; // Transaction History အတွက်
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    // 1. Plan များကို ပြသခြင်း
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return response()->json([
            'status' => true,
            'data' => $plans
        ]);
    }

    // 2. Coin ဖြင့် ဝယ်ယူခြင်း
    public function purchase(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:subscription_plans,id']);
        
        $user = $request->user();
        $plan = SubscriptionPlan::find($request->plan_id);

        // A. Coin လုံလောက်မှု ရှိမရှိ စစ်ခြင်း
        if ($user->coins < $plan->coin_price) {
            return response()->json([
                'status' => false,
                'message' => 'Not enough coins.'
            ], 400);
        }

        // B. Expiry Date တွက်ချက်ခြင်း
        $currentExpiresAt = $user->premium_expires_at ? Carbon::parse($user->premium_expires_at) : Carbon::now();
        
        // လက်ရှိ Premium ဖြစ်နေရင် ရှိပြီးသားရက်ကို ဆက်ပေါင်း၊ မဟုတ်ရင် ဒီနေ့ကစ
        if ($currentExpiresAt->isPast()) {
            $newExpiresAt = Carbon::now()->addDays($plan->duration_days);
        } else {
            $newExpiresAt = $currentExpiresAt->addDays($plan->duration_days);
        }

        // C. Transaction & Update (DB Transaction သုံးထားသည်)
        try {
            DB::beginTransaction();

            // Coin ဖြတ်
            $user->coins -= $plan->coin_price;
            // ရက်တိုး
            $user->premium_expires_at = $newExpiresAt;
            $user->save();

            // History မှတ်
            Transaction::create([
                'user_id' => $user->id,
                'amount' => -$plan->coin_price, 
                'type' => 'subscription',
                'description' => "Bought Premium: {$plan->name}",
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Premium activated successfully!',
                'new_balance' => $user->coins,
                'expires_at' => $newExpiresAt->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error processing transaction.'], 500);
        }
    }
}