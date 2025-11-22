<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\ShowAnime;
use App\Livewire\WatchEpisode;
use App\Livewire\Explore;
use App\Livewire\Topup;
use App\Livewire\Profile;
use App\Livewire\RequestAnime;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Leaderboard;
use App\Livewire\Oracle;
use App\Http\Controllers\StreamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', Home::class)->name('home');
Route::get('/explore', Explore::class)->name('explore');
Route::get('/anime/{slug}', ShowAnime::class)->name('anime.show');
Route::get('/watch/{episode}', WatchEpisode::class)->name('anime.watch');
Route::get('/leaderboard', Leaderboard::class)->name('leaderboard');

Route::get('/stream/{id}', [StreamController::class, 'play'])
    ->middleware('auth') // Middleware နဲ့ ကာတာ ပိုကောင်းပါတယ်
    ->name('stream.play');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/topup', Topup::class)->name('topup');
    Route::get('/request', RequestAnime::class)->name('request');
    Route::get('/oracle', Oracle::class)->name('oracle');
});