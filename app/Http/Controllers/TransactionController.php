<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\ComicChapter; // ✅ ComicChapter Model
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Notifications\SystemNotification;

class TransactionController extends Controller
{
    /**
     * Episode တစ်ခုကို ဝယ်ယူ (Unlock) ရန်။
     */
    public function purchaseEpisode(Request $request, Episode $episode)
    {
        $episode->load('season.anime'); 
        
        $user = $request->user();
        $cost = $episode->coin_price; 

        $animeTitle = $episode->season->anime->title ?? 'Unknown Anime';
        $episodeNumber = $episode->episode_number;
        $episodeId = $episode->id;
        
        if ($cost <= 0) $cost = 0; 

        if ($user->coins < $cost) {
            return response()->json(['message' => 'Insufficient coins.'], 403);
        }

        $epIdIdentifier = 'ep_' . $episodeId . ':'; 

        $alreadyUnlocked = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->where('description', 'like', $epIdIdentifier . '%')
            ->exists();
            
        if ($alreadyUnlocked) {
            return response()->json(['message' => 'This episode is already unlocked.'], 200);
        }

        try {
            DB::beginTransaction();

            $user->decrement('coins', $cost);

            $description = 'ep_' . $episodeId . ':' . $animeTitle . ' - Ep ' . $episodeNumber;

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => $cost,
                'description' => $description,
            ]);
            
            // Notification ပို့ခြင်း
            $user->notify(new SystemNotification(
                "Episode Unlocked: {$animeTitle}", 
                "You unlocked Ep {$episodeNumber}.", 
                'success'
            ));

            DB::commit();

            return response()->json([
                'message' => 'Episode unlocked successfully!',
                'new_coins' => $user->coins
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaction failed.'], 500);
        }
    }

    /**
     * Comic Chapter တစ်ခုကို ဝယ်ယူ (Unlock) ရန်။
     */
    public function purchaseComicChapter(Request $request, $chapterId)
    {
        // 1. Data ရှာဖွေခြင်း
        $chapter = ComicChapter::with('comic')->findOrFail($chapterId);
        $user = $request->user();
        $cost = $chapter->coin_price;

        // 2. စစ်ဆေးမှုများ
        if (!$chapter->is_premium || $cost <= 0) {
            return response()->json(['message' => 'This chapter is free.'], 200);
        }

        // Identifier: "comic_chapter_{id}"
        $identifier = 'comic_chapter_' . $chapter->id;
        
        $alreadyUnlocked = Transaction::where('user_id', $user->id)
            ->where('description', $identifier)
            ->exists();

        if ($alreadyUnlocked) {
            return response()->json(['message' => 'Already unlocked.'], 200);
        }

        if ($user->coins < $cost) {
            return response()->json(['message' => 'Insufficient coins.'], 403);
        }

        // 3. Transaction စတင်ခြင်း
        try {
            DB::beginTransaction();

            // a. Coins ဖြတ်တောက်
            $user->decrement('coins', $cost);

            // b. Record Transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => $cost,
                'description' => $identifier,
            ]);
            
            // c. Notification ပို့ခြင်း (Optional but recommended)
            $comicTitle = $chapter->comic->title ?? 'Comic';
            $user->notify(new SystemNotification(
                "Chapter Unlocked: {$comicTitle}", 
                "You unlocked Chapter {$chapter->chapter_number}.", 
                'success'
            ));

            DB::commit();

            return response()->json([
                'message' => 'Chapter unlocked successfully!',
                'new_coins' => $user->coins
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaction failed.'], 500);
        }
    }
}