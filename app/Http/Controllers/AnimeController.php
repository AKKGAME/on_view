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
        // 🔥 'channel' relation ကို ထည့်သွင်းခြင်း
        $animes = Anime::with(['seasons', 'genres', 'channel']) 
                    ->latest()
                    ->paginate(12);

        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }

    // GET /home/ongoing
    public function getOngoingAnimes()
    {
        // 🔥 'channel' relation ကို ထည့်သွင်းခြင်း
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
        // 🔥 'channel' relation ကို ထည့်သွင်းခြင်း
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

        // 🔥 'channel' relation ကို ထည့်သွင်းခြင်း
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
        // 🔥 'channel' relation ကို ထည့်သွင်းခြင်း (အရေးအကြီးဆုံး)
        $anime = Anime::where('slug', $slug)
                    ->with(['seasons.episodes', 'genres', 'channel'])
                    ->firstOrFail();
                    
        return $this->transformAnime($anime);
    }
}