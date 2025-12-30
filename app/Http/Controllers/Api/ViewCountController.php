<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewCountController extends Controller
{
    public function increment(Request $request)
    {
        $request->validate([
            'type' => 'required|in:anime,movie,comic',
            'id' => 'required|integer',
        ]);

        $table = match ($request->type) {
            'anime' => 'animes',
            'movie' => 'movies',
            'comic' => 'comics',
        };

        // Database Query သုံးပြီး တိုက်ရိုက် update လုပ်ခြင်း (Performance ပိုကောင်း)
        DB::table($table)->where('id', $request->id)->increment('view_count');

        return response()->json(['success' => true]);
    }
}