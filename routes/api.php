<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers Import
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\ComicController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\RedeemController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\ViewCountController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\DailyRewardController;

// Models
use App\Models\Section;

/*
|--------------------------------------------------------------------------
| API Routes (Secured)
|--------------------------------------------------------------------------
*/

// =========================================================================
// 🌍 PUBLIC ROUTES (Login မလိုသော်လည်း API Key နှင့် Rate Limit လိုသည်)
// =========================================================================

Route::middleware(['api.key', 'throttle:60,1'])->group(function () {

    // --- 1. System & Utility ---
    Route::get('/app-version', [AppVersionController::class, 'checkVersion']);
    Route::get('/genres', [UtilityController::class, 'getGenres']);
    Route::get('/payment-methods', [UtilityController::class, 'getPaymentMethods']);
    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/theme-settings', [ThemeController::class, 'getActiveTheme']);

    // --- 2. Authentication (Strict Rate Limiting) ---
    // Login/Register ကို တစ်မိနစ်လျှင် ၁၀ ကြိမ်သာ ခွင့်ပြုမည် (Brute Force ကာကွယ်ရန်)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // --- 3. Search ---
    Route::get('/search', [UtilityController::class, 'search']);

    // --- 4. Comics ---
    Route::get('/comics', [ComicController::class, 'index']);
    Route::get('/comics/{slug}', [ComicController::class, 'show']);

    // --- 5. Movies ---
    Route::get('/movies', [MovieController::class, 'index']);
    Route::get('/movies/search', [MovieController::class, 'search']);
    Route::get('/movies/{slug}', [MovieController::class, 'show']);

    // --- 6. Anime ---
    Route::get('/home/latest', [AnimeController::class, 'getLatestAnimes']);
    Route::get('/home/ongoing', [AnimeController::class, 'getOngoingAnimes']);
    Route::get('/home/top-viewed', [AnimeController::class, 'getTopViewedAnimes']);
    Route::get('/anime/all', [AnimeController::class, 'getAllAnimes']);
    Route::get('/anime/search', [AnimeController::class, 'search']);
    Route::get('/anime/{slug}', [AnimeController::class, 'showBySlug']);
    Route::post('/view-count/increment', [ViewCountController::class, 'increment']);

    // --- 7. Channels ---
    Route::get('/channels', [ChannelController::class, 'index']);
    Route::get('/channels/{id}', [ChannelController::class, 'show']);

    // --- 8. Home Dynamic Sections ---
    Route::get('/home-sections', function () {
        return Section::with(['animes' => function($query) {
            $query->limit(12); // Limit for performance
        }])
        ->where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get();
    });

    Route::get('/home-sections/{section}', function (Section $section) {
        return $section->animes()
            ->orderByPivot('sort_order', 'asc')
            ->paginate(18);
    });
});


// =========================================================================
// 🔐 AUTHENTICATED ROUTES (Login ဝင်ထားသူများသာ)
// =========================================================================

Route::middleware(['auth:sanctum', 'api.key', 'throttle:60,1'])->group(function () {

    // --- 1. Auth & Profile ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'getProfile']);
    Route::post('/user/update', [AuthController::class, 'updateProfile']);
    Route::get('/user/library', [UserController::class, 'getLibrary']);
    
    // --- Daily Reward ---
    Route::get('/daily-reward/status', [DailyRewardController::class, 'status']);
    Route::post('/daily-reward/claim', [DailyRewardController::class, 'claim']);
    
    // --- 2. Transactions & Wallet ---
    Route::get('/user/topup-history', [UserController::class, 'getTopupHistory']);
    Route::get('/user/transactions', [UserController::class, 'getTransactions']);
    Route::post('/topup/request', [RequestController::class, 'submitTopupRequest']);

    // --- 3. Anime Streaming ---
    Route::get('/stream/play/{id}', [StreamController::class, 'play']);
    Route::post('/purchase/episode/{episode}', [TransactionController::class, 'purchaseEpisode']);
    Route::post('/request/anime', [RequestController::class, 'submitAnimeRequest']);

    // --- 4. Comic Reading ---
    Route::get('/comics/chapter/{id}/read', [ComicController::class, 'readChapter']);
    Route::post('/comics/chapter/{id}/purchase', [TransactionController::class, 'purchaseComicChapter']);

    // --- 5. Watch History & Watchlist ---
    Route::post('/watch/episode/{episode_id}', [HistoryController::class, 'updateWatchHistory']);
    Route::get('/user/watch-history', [HistoryController::class, 'getWatchHistory']);
    Route::delete('/user/watch-history/{watchHistory}', [HistoryController::class, 'destroy']);
    Route::post('/user/watch-history/clear', [HistoryController::class, 'clearAll']);
    
    Route::get('/user/watchlist', [HistoryController::class, 'getWatchlist']);
    Route::post('/watchlist/toggle/{anime}', [HistoryController::class, 'toggleWatchlist']);

    // --- 6. Notifications ---
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', function (Request $request) {
        return response()->json(['count' => $request->user()->unreadNotifications->count()]);
    });
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll']);

    // --- 7. Subscription ---
    Route::get('/subscription/plans', [SubscriptionController::class, 'index']);
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase']);

    // --- 8. Movie Purchase ---
    Route::post('/purchase/movie/{id}', [MovieController::class, 'purchase']);

    // --- 9. Redeem ---
    Route::post('/redeem-coupon', [RedeemController::class, 'redeem']);
    Route::get('/redeem-history', [RedeemController::class, 'getHistory']);

});