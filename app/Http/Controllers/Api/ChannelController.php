<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    // Channel အားလုံးပြမယ် (Grid View အတွက်)
    public function index()
    {
        // Model တွင် 'full_logo_url' append လုပ်ထားမှ အဆင်ပြေမည်
        $channels = Channel::where('is_active', true)
            ->latest()
            ->get()
            ->makeHidden(['created_at', 'updated_at']); // မလိုတာတွေ ဖျောက်မယ်

        return response()->json(['data' => $channels]);
    }

    // Channel Detail နဲ့ သူတင်ထားတဲ့ Content တွေပြမယ်
    public function show($id)
    {
        $channel = Channel::where('is_active', true)
            ->with([
                // Anime များကို နောက်ဆုံးတင်ထားသည့်အစဉ်လိုက် ၁၀ ခုယူမယ်
                'animes' => function ($query) {
                    $query->select('id', 'channel_id', 'title', 'slug', 'thumbnail_url', 'view_count') // လိုတာပဲယူမယ်
                          ->latest()
                          ->limit(10);
                },
                
                // Movie များကို Published ဖြစ်တာပဲယူမယ်
                'movies' => function ($query) {
                    $query->select('id', 'channel_id', 'title', 'slug', 'thumbnail_url', 'view_count')
                          ->where('is_published', true)
                          ->latest()
                          ->limit(10);
                },

                // Comic များ (Ongoing ရော Finished ရောပြမယ်)
                'comics' => function ($query) {
                    $query->select('id', 'channel_id', 'title', 'slug', 'cover_image', 'view_count')
                          ->latest()
                          ->limit(10);
                },
            ])
            ->find($id); // findOrFail အစား find ကိုသုံးပါ (API Error မတက်အောင်)

        if (!$channel) {
            return response()->json(['message' => 'Channel not found'], 404);
        }

        return response()->json(['data' => $channel]);
    }
}