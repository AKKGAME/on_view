<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WatchHistory;
use App\Models\Anime;
use App\Models\Episode;

class HistoryController extends Controller
{
    // POST /watch/episode/{episode_id}
    public function updateWatchHistory(Request $request, $episode_id)
    {
        $episode = Episode::with('season.anime')->findOrFail($episode_id);
        $anime_id = $episode->season->anime->id; 

        WatchHistory::updateOrCreate(
            ['user_id' => $request->user()->id, 'episode_id' => $episode_id],
            ['anime_id' => $anime_id, 'updated_at' => now()] 
        );
        return response()->json(['message' => 'History updated'], 200);
    }
    
    // GET /user/watchlist
    public function getWatchlist(Request $request)
    {
        $animes = $request->user()->watchlist()->with('genres')->get();
        return $animes->map(function($anime) {
            $data = $anime->toArray();
            $data['cover_url'] = $anime->thumbnail_url; 
            return $data;
        });
    }

    // POST /watchlist/toggle/{anime}
    public function toggleWatchlist(Request $request, Anime $anime)
    {
        $request->user()->watchlist()->toggle($anime->id);
        $isInWatchlist = $request->user()->watchlist()->where('anime_id', $anime->id)->exists();
        return response()->json([
            'message' => $isInWatchlist ? 'Added to watchlist' : 'Removed from watchlist',
            'is_in_watchlist' => $isInWatchlist
        ]);
    }

    // GET /user/watch-history
    public function getWatchHistory(Request $request)
    {
        $histories = WatchHistory::where('user_id', $request->user()->id)
            ->with(['episode', 'anime']) 
            ->latest('updated_at') // latest() by updated_at is better for history
            ->take(50)
            ->get();

        return $histories->map(function ($item) {
            
            if (!$item->episode || !$item->anime) {
                return null;
            }

            return [
                'id' => $item->id, // History Row ID
                'episode_id' => $item->episode->id, // ✅ အရေးကြီးဆုံး: Episode ID ထည့်ပေးလိုက်ပါပြီ
                'anime_title' => $item->anime->title,
                'episode_number' => $item->episode->episode_number,
                'episode_title' => $item->episode->title,
                'cover_url' => $item->episode->thumbnail_url ?? $item->anime->cover_image, // Fallback image
                'slug' => $item->anime->slug,
                'watched_at' => $item->updated_at->diffForHumans(),
            ];
        })->filter()->values();
    }
    
    // DELETE /user/watch-history/{watchHistory}
    public function destroy(Request $request, WatchHistory $watchHistory)
    {
        if ($request->user()->id !== $watchHistory->user_id) {
            abort(403, 'You do not have permission to delete this watch history.');
        }

        $watchHistory->delete();
        return response()->json(null, 204);
    }
    
    // Clear All Watch History
    public function clearAll(Request $request)
    {
        WatchHistory::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'Watch history cleared successfully.'
        ], 200);
    }
}