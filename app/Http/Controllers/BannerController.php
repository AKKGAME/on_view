<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    /**
     * GET /api/banners
     * Flutter App အတွက် လက်ရှိ Active ဖြစ်နေသော Banners များကို ရယူသည်
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // လက်ရှိအချိန်ကို ရယူခြင်း
        $now = now(); 

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