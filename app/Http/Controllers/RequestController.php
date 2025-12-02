<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentRequest;
use App\Models\AnimeRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    // POST /topup/request
    public function submitTopupRequest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500',
            'phone_last_digits' => 'required|numeric|digits_between:3,6',
            'screenshot' => 'required|image|max:2048', 
            'payment_method' => 'required|string'
        ]);

        $path = $request->file('screenshot')->store('payment-slips', 'public');

        PaymentRequest::create([
            'user_id' => $request->user()->id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'phone_last_digits' => $request->phone_last_digits,
            'screenshot_path' => $path,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Top-up request submitted.'], 201);
    }

    // POST /request/anime
    public function submitAnimeRequest(Request $request)
    {
        $request->validate([
            'title' => 'required|min:3|max:255',
            'note' => 'nullable|max:500',
        ]);
        
        $cost = 50; 
        $user = $request->user();

        if ($user->coins < $cost) {
            return response()->json(['message' => 'Insufficient coins.'], 403);
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

        return response()->json(['message' => 'Request submitted.', 'cost' => $cost], 201);
    }
}