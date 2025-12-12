<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Http\Resources\MovieResource;
use Illuminate\Http\Request;
use App\Models\Transaction;

class MovieController extends Controller
{
    // 1. Get All Movies (Latest First)
    public function index()
    {
        $movies = Movie::where('is_published', true)
            ->with('genres') // Genre တွေပါ တွဲခေါ်မယ်
            ->latest()
            ->paginate(12); // တစ်ခါခေါ်ရင် ၁၂ ကားပြမယ်

        return MovieResource::collection($movies);
    }

    // 2. Get Single Movie Detail
    public function show($slug)
    {
        $movie = Movie::where('slug', $slug)
            ->where('is_published', true)
            ->with('genres')
            ->firstOrFail();

        return new MovieResource($movie);
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
            
        return MovieResource::collection($movies);
    }

    // 4. Purchase Movie
    public function purchase(Request $request, $id)
    {
        $user = $request->user();
        $movie = Movie::findOrFail($id);

        // (A) စစ်ဆေးခြင်းများ (Validation)
        
        // 1. Premium မဟုတ်ရင် ဝယ်စရာမလို (Free)
        if (!$movie->is_premium) {
            return response()->json(['message' => 'This movie is free.'], 400);
        }

        // 2. ဝယ်ပြီးသား ဖြစ်နေလား စစ်မယ်
        if ($user->hasPurchasedMovie($movie->id)) {
            return response()->json(['message' => 'You already own this movie.'], 400);
        }

        // 3. ပိုက်ဆံလောက်လား စစ်မယ်
        if ($user->coins < $movie->coin_price) {
            return response()->json(['message' => 'Insufficient balance.'], 402);
        }

        // (B) ဝယ်ယူခြင်း လုပ်ငန်းစဉ် (Database Transaction သုံးမယ်)
        DB::beginTransaction();
        try {
            // 1. Coin ဖြတ်မယ်
            $user->decrement('coins', $movie->coin_price);

            // 2. Movie ကို User နဲ့ ချိတ်မယ် (Ownership ပေးမယ်)
            $user->purchasedMovies()->attach($movie->id, ['price' => $movie->coin_price]);

            // 3. Transaction History မှတ်မယ် (Optional - App မှာ Transaction Table ရှိရင် သုံးပါ)
            Transaction::create([
                'user_id' => $user->id,
                'amount' => -$movie->coin_price,
                'type' => 'movie_purchase',
                'description' => "Unlocked movie: {$movie->title}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movie unlocked successfully!',
                'current_coins' => $user->coins,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Purchase failed. Try again.'], 500);
        }
    }
}