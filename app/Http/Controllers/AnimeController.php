<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    // ✅ Helper: Data Transform
    private function transformAnime($anime) {
        $data = $anime->toArray();
        $data['cover_url'] = $anime->cover_image;
        
        // Latest Episode Logic
        $latestSeason = $anime->seasons->sortByDesc('id')->first();
        $latestEpisode = $latestSeason ? $latestSeason->episodes->sortByDesc('episode_number')->first() : null;
        
        $data['latest_episode'] = $latestEpisode ? $latestEpisode->episode_number : 0;

        return $data;
    }

    // GET /home/latest
    public function getLatestAnimes()
    {
        $animes = Anime::with(['seasons', 'genres', 'channel']) 
                    ->latest()
                    ->paginate(12);

        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }

    // GET /home/top-viewed (View အများဆုံး ၁၀ ခု)
    public function getTopViewedAnimes()
    {
        $animes = Anime::with(['seasons', 'genres', 'channel'])
                    ->orderBy('view_count', 'desc') 
                    ->take(10)
                    ->get();

        $formattedData = $animes->map(function ($anime) {
            return $this->transformAnime($anime);
        });

        return response()->json($formattedData);
    }

    // GET /home/ongoing
    public function getOngoingAnimes()
    {
        $animes = Anime::where('is_completed', false)
                    ->with(['genres', 'channel', 'seasons.episodes' => function($q) {
                        $q->orderBy('episode_number', 'desc');
                    }])
                    ->latest()
                    ->paginate(12);
                    
        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }

    // GET /anime/all
    public function getAllAnimes()
    {
        $animes = Anime::with(['seasons', 'genres', 'channel'])
                    ->latest()
                    ->paginate(12);
        
        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }
    
    // GET /api/anime/search
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        $animes = Anime::where('title', 'LIKE', "%{$query}%")
                    ->with(['seasons', 'genres', 'channel'])
                    ->take(20)
                    ->get();

        $formattedData = $animes->map(function ($anime) {
            return $this->transformAnime($anime);
        });
    
        return response()->json($formattedData);
    }
    
    // GET /anime/{slug}
    public function showBySlug($slug)
    {
        // 🔥 ပြင်ဆင်ထားသောနေရာ: Subtitles ကိုပါ ဆွဲထုတ်ခြင်း
        $anime = Anime::where('slug', $slug)
                    ->with([
                        // 'seasons.episodes' အစား 'seasons.episodes.subtitles' လို့ပြောင်းလိုက်ပါတယ်
                        'seasons.episodes.subtitles', 
                        'genres', 
                        'channel'
                    ])
                    ->firstOrFail();
                    
        // View Count တိုးခြင်း (Optional)
        // $anime->increment('view_count');

        return $this->transformAnime($anime);
    }
}