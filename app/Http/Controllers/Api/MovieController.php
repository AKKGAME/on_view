<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\BunnyStream; // 🔥 BunnyCDN Service Import

class MovieController extends Controller
{
    // ✅ HELPER: Data Transform & URL Security
    private function transformMovie($movie, $checkAccess = false) {
        $data = $movie->toArray();

        // 1. Image URL အပြည့်အစုံ ထုတ်ပေးခြင်း
        if (!empty($movie->poster)) {
            $data['cover_url'] = asset('storage/' . $movie->poster); // Column Name 'poster' or 'cover_image' (ကိုယ့် DB အတိုင်းကြည့်ပြင်ပါ)
        } elseif (!empty($movie->cover_image)) {
            $data['cover_url'] = asset('storage/' . $movie->cover_image);
        } else {
            $data['cover_url'] = null;
        }

        // 2. Video URL ကို ပုံမှန်အားဖြင့် ဖျောက်ထားမယ် (List တွေမှာ မပါစေချင်လို့)
        unset($data['video_url']); 

        // 3. Detail ကြည့်တဲ့အခါ Access ရှိမှသာ URL ထည့်ပေးမယ်
        if ($checkAccess && !empty($movie->video_url)) {
            // User ကိုစစ်မယ်
            $user = Auth::guard('sanctum')->user();
            $canWatch = false;

            // Access Logic
            if (!$movie->is_premium) {
                $canWatch = true; // Free Movie
            } elseif ($user) {
                if ($user->is_premium) {
                    $canWatch = true; // VIP User
                } elseif ($user->hasPurchasedMovie($movie->id)) {
                    $canWatch = true; // ဝယ်ပြီးသား User
                }
            }

            // ကြည့်ခွင့်ရှိမှ Token နဲ့ Sign လုပ်ပြီး Link ပေးမယ်
            if ($canWatch) {
                if (class_exists(BunnyStream::class)) {
                     // BunnyCDN Signing (300 minutes)
                     $path = parse_url($movie->video_url, PHP_URL_PATH);
                     $data['video_url'] = BunnyStream::signUrl($path, 300);
                } else {
                     $data['video_url'] = $movie->video_url; // Service မရှိရင် Direct URL
                }
                $data['is_unlocked'] = true;
            } else {
                $data['is_unlocked'] = false;
            }
        }

        return $data;
    }

    // 1. Get All Movies (Latest First)
    public function index()
    {
        $movies = Movie::where('is_published', true)
            ->with('genres')
            ->latest()
            ->paginate(12);

        // Resource အစား Helper Function သုံးပြီး Transform လုပ်မယ်
        $movies->getCollection()->transform(function ($movie) {
            return $this->transformMovie($movie, false); // List မှာ Video URL မထည့်ဘူး
        });

        return $movies;
    }

    // 2. Get Single Movie Detail
    public function show($slug)
    {
        $movie = Movie::where('slug', $slug)
            ->where('is_published', true)
            ->with('genres')
            ->firstOrFail();

        // 🔥 View Count တိုးမယ်
        $movie->increment('view_count');

        // 🔥 Detail ဖြစ်တဲ့အတွက် Access စစ်ပြီး Video URL ထည့်ပေးမယ်
        $data = $this->transformMovie($movie, true);

        return response()->json(['data' => $data]);
    }
    
    // 3. Search Movies
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $movies = Movie::where('is_published', true)
            ->where('title', 'like', "%{$query}%")
            ->latest()
            ->take(20)
            ->get();
            
        $formattedData = $movies->map(function ($movie) {
            return $this->transformMovie($movie, false);
        });
            
        return response()->json(['data' => $formattedData]);
    }

    // 4. Purchase Movie
    public function purchase(Request $request, $id)
    {
        $user = $request->user();
        $movie = Movie::findOrFail($id);

        // (A) Validation
        if (!$movie->is_premium) {
            return response()->json(['message' => 'This movie is free.'], 400);
        }

        if ($user->hasPurchasedMovie($movie->id)) {
            return response()->json(['message' => 'You already own this movie.'], 400);
        }

        if ($user->coins < $movie->coin_price) {
            return response()->json(['message' => 'Insufficient balance.'], 402);
        }

        // (B) Purchase Transaction
        DB::beginTransaction();
        try {
            // 1. Cut Coins
            $user->decrement('coins', $movie->coin_price);

            // 2. Attach Ownership
            // purchasedMovies relationship မရှိသေးရင် User Model မှာ စစ်ပါ
            $user->purchasedMovies()->attach($movie->id, ['price' => $movie->coin_price]);

            // 3. Transaction Record
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $movie->coin_price,
                'type' => 'purchase', // 'purchase' လို့ထားတာ ပိုကောင်းပါတယ် (StreamController နဲ့ ညှိလို့ရအောင်)
                'description' => "mov_{$movie->id}: {$movie->title}", // Format: mov_ID
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movie unlocked successfully!',
                'new_coins' => $user->coins, // Flutter ဘက်မှာ Update လုပ်ဖို့
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Purchase failed. ' . $e->getMessage()], 500);
        }
    }
}