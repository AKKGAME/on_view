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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 🌍 PUBLIC ROUTES (Login မဝင်ဘဲ သုံးလို့ရသည်)
// ==========================================

// 1. System & Utility
Route::get('/app-version', [AppVersionController::class, 'checkVersion']);
Route::get('/genres', [UtilityController::class, 'getGenres']);
Route::get('/payment-methods', [UtilityController::class, 'getPaymentMethods']);
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/theme-settings', [ThemeController::class, 'getActiveTheme']);

// 2. Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']); // 🔥 Single Device Logic is inside Controller

// 3. Search (Public Search)
Route::get('/search', [UtilityController::class, 'search']);

// 4. Comics (Public View)
Route::get('/comics', [ComicController::class, 'index']);
Route::get('/comics/{slug}', [ComicController::class, 'show']);

// 5. Movies
Route::get('/movies', [MovieController::class, 'index']); // All Movies
Route::get('/movies/search', [MovieController::class, 'search']); // Search
Route::get('/movies/{slug}', [MovieController::class, 'show']); // Detail

// 6. Anime
Route::get('/home/latest', [AnimeController::class, 'getLatestAnimes']); // Home Screen
Route::get('/home/ongoing', [AnimeController::class, 'getOngoingAnimes']); // Ongoing
Route::get('/anime/all', [AnimeController::class, 'getAllAnimes']); // All Anime Screen
Route::get('/anime/search', [AnimeController::class, 'search']); // Search
Route::get('/anime/{slug}', [AnimeController::class, 'showBySlug']); // Detail Screen
Route::post('/view-count/increment', [ViewCountController::class, 'increment']);

Route::get('/channels', [ChannelController::class, 'index']);
Route::get('/channels/{id}', [ChannelController::class, 'show']);


// ==========================================
// 🔐 AUTHENTICATED ROUTES (Login ဝင်မှရမည်)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // --- 1. Auth & Profile ---
    Route::post('/logout', [AuthController::class, 'logout']); // 🔥 Resets Device ID inside Controller
    Route::get('/user', [UserController::class, 'getProfile']);
    Route::post('/user/update', [UserController::class, 'updateProfile']);
    Route::get('/user/library', [UserController::class, 'getLibrary']);
    
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