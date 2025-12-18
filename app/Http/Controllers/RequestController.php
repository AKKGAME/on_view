<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentRequest;
use App\Models\AnimeRequest;
use App\Models\Transaction;
use App\Models\User; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // ✅ HTTP Facade ထည့်ရန် မမေ့ပါနှင့်

// Filament Notification များကို ခေါ်သုံးရန်
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

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

        $paymentRequest = PaymentRequest::create([
            'user_id' => $request->user()->id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'phone_last_digits' => $request->phone_last_digits,
            'screenshot_path' => $path,
            'status' => 'pending'
        ]);

        // --- NOTIFICATIONS START ---
        try {
            $user = $request->user();
            $admin = User::find(1);

            // 1. Send to Filament Admin Panel
            if ($admin) {
                Notification::make()
                    ->title('New Topup Request 💰')
                    ->body("User: {$user->name} | Amount: {$request->amount} MMK")
                    ->success()
                    ->icon('heroicon-o-currency-dollar')
                    ->sendToDatabase($admin);
            }

            // 2. Send to Telegram
            $msg = "<b>💰 New Topup Request!</b>\n" .
                   "👤 User: {$user->name}\n" .
                   "📞 Last 6 Digits: {$request->phone_last_digits}\n" .
                   "💸 Amount: {$request->amount} MMK\n" .
                   "🏦 Method: {$request->payment_method}";
            
            $this->sendTelegramMessage($msg);

        } catch (\Exception $e) {
            Log::error("Topup Noti Error: " . $e->getMessage());
        }
        // --- NOTIFICATIONS END ---

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

        $animeRequest = AnimeRequest::create([
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

        // --- NOTIFICATIONS START ---
        try {
            $admin = User::find(1);

            // 1. Send to Filament Admin Panel
            if ($admin) {
                Notification::make()
                    ->title('New Anime Request 🎬')
                    ->body("User wants: {$request->title}")
                    ->warning()
                    ->icon('heroicon-o-film') 
                    ->sendToDatabase($admin);
            }

            // 2. Send to Telegram
            $msg = "<b>🎬 New Anime Request!</b>\n" .
                   "👤 User: {$user->name}\n" .
                   "📺 Title: {$request->title}\n" .
                   "📝 Note: " . ($request->note ?? 'None');

            $this->sendTelegramMessage($msg);

        } catch (\Exception $e) {
            Log::error("Anime Request Noti Error: " . $e->getMessage());
        }
        // --- NOTIFICATIONS END ---

        return response()->json(['message' => 'Request submitted.', 'cost' => $cost], 201);
    }

    /**
     * Private Helper Function to Send Telegram Message
     */
    private function sendTelegramMessage($message)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if ($token && $chatId) {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML', // စာလုံးအမည်း/အစောင်း သုံးလို့ရအောင်
            ]);
        }
    }
}