<?php

namespace App\Http\Controllers;

use App\Models\Banner; // ✅ Must be present
use Illuminate\Http\Request;
// NOTE: DB is not used here, but keeping it doesn't hurt.
// use Illuminate\Support\Facades\DB; 
use Carbon\Carbon; // ✅ Use Carbon explicitly if 'now()' causes issues on some servers

class BannerController extends Controller
{
    /**
     * GET /api/banners
     * Flutter App အတွက် လက်ရှိ Active ဖြစ်နေသော Banners များကို ရယူသည်
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // 1. လက်ရှိအချိန်ကို Carbon Object အဖြစ် ရယူခြင်း
        $now = Carbon::now(); // Using Carbon explicitly for clarity and robustness

        $banners = Banner::where('is_active', true)
            // 1. Start Date စစ်ဆေးခြင်း: null ဖြစ်ရင် ဒါမှမဟုတ် ယခုအချိန်ထက် စော/ညီနေရင်
            ->where(function($query) use ($now) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', $now);
            })
            // 2. End Date စစ်ဆေးခြင်း: null ဖြစ်ရင် ဒါမှမဟုတ် ယခုအချိန်ထက် နောက်ကျ/ညီနေရင်
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            // Order နဲ့ ID ဖြင့် စီစဉ်ခြင်း
            ->orderBy('order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($banners);
    }
}