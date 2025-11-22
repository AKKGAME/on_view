<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Anime;
use App\Models\WatchHistory;
use App\Models\PaymentRequest; // Import PaymentRequest
use App\Models\AnimeRequest; // Import AnimeRequest
use App\Models\Transaction; // Import Transaction
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // File upload အတွက်

// 1. Authentication (Login / Register)
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|numeric|unique:users,phone',
        'password' => 'required|string|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'rank' => 'Newbie',
        'coins' => 100,
        'xp' => 0,
    ]);

    $token = $user->createToken('flutter-token')->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user], 201);
});

Route::post('/login', function (Request $request) {
    $request->validate(['phone' => 'required|numeric', 'password' => 'required']);
    
    $user = User::where('phone', $request->phone)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    
    $token = $user->createToken('flutter-token')->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user], 200);
});

// 2. Authenticated Routes (Requires Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // 2.1. User Profile & Stats
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user, 
            'coins' => $user->coins, 
            'xp' => $user->xp, 
            'rank' => $user->rank,
            'referral_code' => $user->referral_code,
        ]);
    });

    // 2.2. Home Screen Data (Latest Animes)
    Route::get('/home/latest', function () {
        return Anime::with('seasons')->latest()->take(10)->get();
    });
    
    // 2.3. Anime Detail
    Route::get('/anime/{slug}', function ($slug) {
        return Anime::where('slug', $slug)->with('seasons.episodes')->firstOrFail();
    });

    // 2.4. Update Watch History
    Route::post('/watch/episode/{episode}', function (Request $request, $episode) {
        WatchHistory::updateOrCreate(
            ['user_id' => $request->user()->id, 'episode_id' => $episode],
            ['updated_at' => now()]
        );
        return response()->json(['message' => 'History updated'], 200);
    });

    // 2.5. Topup Request Submission
    Route::post('/topup/request', function (Request $request) {
        $request->validate([
            'amount' => 'required|numeric|min:500',
            'phone_last_digits' => 'required|numeric|digits_between:3,6',
            'screenshot' => 'required|image|max:2048', // Flutter sends file as 'screenshot'
            'payment_method' => 'required|in:kpay,wave'
        ]);

        $path = $request->file('screenshot')->store('payment-slips', 'public');

        PaymentRequest::create([
            'user_id' => $request->user()->id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'phone_last_digits' => $request->phone_last_digits,
            'screenshot_path' => $path,
        ]);

        return response()->json(['message' => 'Top-up request submitted. Check history for status.'], 201);
    });

    // 2.6. Anime Request Submission
    Route::post('/request/anime', function (Request $request) {
        $request->validate([
            'title' => 'required|min:3|max:255',
            'note' => 'nullable|max:500',
        ]);
        
        $cost = 50; 
        $user = $request->user();

        if ($user->coins < $cost) {
            return response()->json(['message' => 'Insufficient coins to submit request.'], 403);
        }

        $user->decrement('coins', $cost);

        AnimeRequest::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'note' => $request->note,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'purchase',
            'amount' => $cost,
            'description' => 'Requested Anime: ' . $request->title,
        ]);

        return response()->json(['message' => 'Anime request submitted successfully.', 'cost' => $cost], 201);
    });

    // 2.7. Watchlist Toggle (Add/Remove from Favorites)
    Route::post('/watchlist/toggle/{anime}', function (Request $request, Anime $anime) {
        $request->user()->watchlist()->toggle($anime->id);
        $isInWatchlist = $request->user()->watchlist()->where('anime_id', $anime->id)->exists();
        return response()->json([
            'message' => $isInWatchlist ? 'Added to watchlist' : 'Removed from watchlist',
            'is_in_watchlist' => $isInWatchlist
        ]);
    });

    // 2.8. Get User Library (Unlocked Animes)
    Route::get('/user/library', function (Request $request) {
        $user = $request->user();
        
        $purchasedEpisodeIds = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->pluck('description')
            ->map(fn ($desc) => str_replace('ep_', '', $desc))
            ->unique()
            ->toArray();

        $libraryAnimes = Anime::whereHas('seasons.episodes', function ($query) use ($purchasedEpisodeIds) {
            $query->whereIn('id', $purchasedEpisodeIds);
        })
        ->with([
            // Ensure only seasons/episodes the user actually bought are returned
            'seasons' => function ($query) use ($purchasedEpisodeIds) {
                $query->whereHas('episodes', function ($q) use ($purchasedEpisodeIds) {
                    $q->whereIn('id', $purchasedEpisodeIds);
                });
            },
            'seasons.episodes' => function ($query) use ($purchasedEpisodeIds) {
                $query->whereIn('id', $purchasedEpisodeIds);
            }
        ])
        ->distinct()
        ->get();

        return response()->json($libraryAnimes);
    });

    // 2.9. Get Topup Request History (for User Profile)
    Route::get('/user/topup-history', function (Request $request) {
        $history = PaymentRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($history);
    });
});