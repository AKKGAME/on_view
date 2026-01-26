<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Website ပင်မစာမျက်နှာကို လာရင် ဘာမှမပြဘဲ 404 (Not Found) ပဲ ပြလိုက်မယ်
// ဒါမှမဟုတ် "API Server is running" လို့ပဲ စာတိုလေးပြလိုက်ပါ
Route::get('/', function () {
    return abort(404); 
    // သို့မဟုတ်
    // return "System is running...";
});

// Login route ကို လာရှာရင်လည်း 404 ပဲ ပြမယ် (Admin က သူ့ URL သူသိပြီးသားမို့လို့ပါ)
Route::get('/login', function () {
    return abort(404);
})->name('login');