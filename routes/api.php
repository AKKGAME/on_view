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
use App\Http\Controllers\AppVersionController; // ✅ NEW: For Version Check

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 🌍 PUBLIC ROUTES (Login မဝင်ဘဲ သုံးလို့ရသည်)
// ==========================================

// 1. System & Utility
Route::get('/app-version', [AppVersionController::class, 'checkVersion']); // ✅ App Update စစ်ရန်
Route::get('/genres', [UtilityController::class, 'getGenres']);
Route::get('/payment-methods', [UtilityController::class, 'getPaymentMethods']);
Route::get('/banners', [BannerController::class, 'index']);

// 2. Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 3. Search (Public Search)
Route::get('/search', [UtilityController::class, 'search']); // ✅ Search Anime/Comic

// 4. Comics (Public View)
Route::get('/comics', [ComicController::class, 'index']);
Route::get('/comics/{slug}', [ComicController::class, 'show']);


// ==========================================
// 🔐 AUTHENTICATED ROUTES (Login ဝင်မှရမည်)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // --- 1. Auth & Profile ---
    Route::post('/logout', [AuthController::class, 'logout']); // ✅ Logout
    Route::get('/user', [UserController::class, 'getProfile']);
    Route::post('/user/update', [UserController::class, 'updateProfile']); // ✅ Update Name/Avatar
    Route::get('/user/library', [UserController::class, 'getLibrary']); // Purchased items
    
    // --- 2. Transactions & Wallet ---
    Route::get('/user/topup-history', [UserController::class, 'getTopupHistory']);
    Route::get('/user/transactions', [UserController::class, 'getTransactions']); // Coin usage history
    Route::post('/topup/request', [RequestController::class, 'submitTopupRequest']);

    // --- 3. Anime Streaming ---
    Route::get('/home/latest', [AnimeController::class, 'getLatestAnimes']);
    Route::get('/anime/all', [AnimeController::class, 'getAllAnimes']);
    Route::get('/anime/{slug}', [AnimeController::class, 'showBySlug']);
    Route::get('/stream/play/{id}', [StreamController::class, 'play']); // Get Video URL
    Route::post('/purchase/episode/{episode}', [TransactionController::class, 'purchaseEpisode']);
    Route::post('/request/anime', [RequestController::class, 'submitAnimeRequest']);

    // --- 4. Comic Reading ---
    Route::get('/comics/chapter/{id}/read', [ComicController::class, 'readChapter']);
    Route::post('/comics/chapter/{id}/purchase', [TransactionController::class, 'purchaseComicChapter']);

    // --- 5. Watch History & Watchlist ---
    Route::post('/watch/episode/{episode_id}', [HistoryController::class, 'updateWatchHistory']);
    Route::get('/user/watch-history', [HistoryController::class, 'getWatchHistory']);
    Route::delete('/user/watch-history/{watchHistory}', [HistoryController::class, 'destroy']); // Delete single
    Route::post('/user/watch-history/clear', [HistoryController::class, 'clearAll']); // ✅ Clear All History
    
    Route::get('/user/watchlist', [HistoryController::class, 'getWatchlist']);
    Route::post('/watchlist/toggle/{anime}', [HistoryController::class, 'toggleWatchlist']);

    // --- 6. Notifications ---
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', function (Request $request) {
        return response()->json(['count' => $request->user()->unreadNotifications->count()]);
    }); // Simplified closure
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll']);

    Route::get('/subscription/plans', [SubscriptionController::class, 'index']);
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase']);

});