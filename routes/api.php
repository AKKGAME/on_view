<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller များကို တစ်ခါတည်း သတ်မှတ်နိုင်ရန်
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
// use App\Http\Controllers\BannerController;


// ✅ PUBLIC ROUTE: Get Genres (for Flutter Home Screen)
Route::get('/genres', [UtilityController::class, 'getGenres']);
Route::get('/payment-methods', [UtilityController::class, 'getPaymentMethods']);

// --- 1. PUBLIC AUTHENTICATION ROUTES ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/banners', ['App\\Http\\Controllers\\BannerController', 'index']);


// _________________________________________________
// 🔐 AUTHENTICATED ROUTES (auth:sanctum လိုအပ်ပါသည်)
// _________________________________________________
Route::middleware('auth:sanctum')->group(function () {
    
    // 2.1. User Profile & Data
    Route::get('/user', [UserController::class, 'getProfile']);
    Route::get('/user/library', [UserController::class, 'getLibrary']);
    Route::get('/user/topup-history', [UserController::class, 'getTopupHistory']);

    // 2.2. Anime Data & Details
    Route::get('/home/latest', [AnimeController::class, 'getLatestAnimes']);
    Route::get('/anime/all', [AnimeController::class, 'getAllAnimes']);
    Route::get('/anime/{slug}', [AnimeController::class, 'showBySlug']);
    Route::get('/stream/play/{id}', [StreamController::class, 'play']);
    Route::post('/purchase/episode/{episode}', [TransactionController::class, 'purchaseEpisode']);

    // Read Chapter (Premium check ပါဝင်သည်)
    Route::get('/comics/chapter/{id}/read', [ComicController::class, 'readChapter']);

    // Buy Chapter
    Route::post('/comics/chapter/{id}/purchase', [TransactionController::class, 'purchaseComicChapter']);

    // 2.3. History & Watchlist
    Route::post('/watch/episode/{episode_id}', [HistoryController::class, 'updateWatchHistory']);
    Route::get('/user/watchlist', [HistoryController::class, 'getWatchlist']);
    Route::post('/watchlist/toggle/{anime}', [HistoryController::class, 'toggleWatchlist']);
    Route::get('/user/watch-history', [HistoryController::class, 'getWatchHistory']);
    
    // Watch History ID ကို Route Model Binding သုံးပြီး ဖျက်ခြင်း
    Route::delete('/user/watch-history/{watchHistory}', [HistoryController::class, 'destroy']); 
    
    // 2.4. Requests
    Route::post('/topup/request', [RequestController::class, 'submitTopupRequest']);
    Route::post('/request/anime', [RequestController::class, 'submitAnimeRequest']);
    
    // Get all notifications (read/unread)
    Route::get('/notifications', [NotificationController::class, 'index']);
    
    // Get unread count (for UI badge)
    Route::get('/notifications/unread-count', function (Request $request) {
        return response()->json(['count' => $request->user()->unreadNotifications->count()]);
    });
    
    // Mark a single notification as read
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    
    // Delete a single notification
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
    
    // Clear all notifications
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll']);
});