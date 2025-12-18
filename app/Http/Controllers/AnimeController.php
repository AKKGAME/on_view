<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    // ✅ Helper: Data တစ်ခုချင်းစီကို ပုံစံချခြင်း
    private function transformAnime($anime) {
        $data = $anime->toArray();
        $data['cover_url'] = $anime->cover_image;
        
        // နောက်ဆုံး Episode နံပါတ်ကို ရှာဖွေခြင်း
        $latestSeason = $anime->seasons->sortByDesc('id')->first();
        $latestEpisode = $latestSeason ? $latestSeason->episodes->sortByDesc('episode_number')->first() : null;
        
        $data['latest_episode'] = $latestEpisode ? $latestEpisode->episode_number : 0;

        return $data;
    }

    // GET /home/latest
    public function getLatestAnimes()
    {
        // paginate(12) ကိုသုံးလိုက်ပါပြီ
        $animes = Anime::with(['seasons', 'genres'])
                    ->latest()
                    ->paginate(12);

        // Paginator data ကို transform လုပ်ခြင်း
        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }

    // GET /home/ongoing
    public function getOngoingAnimes()
    {
        $animes = Anime::where('is_completed', false)
                    ->with(['genres', 'seasons.episodes' => function($q) {
                        $q->orderBy('episode_number', 'desc');
                    }])
                    ->latest()
                    ->paginate(12); // Pagination 12
                    
        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }

    // GET /anime/all
    public function getAllAnimes()
    {
        $animes = Anime::with(['seasons', 'genres'])
                    ->latest()
                    ->paginate(12); // Pagination 12
        
        $animes->getCollection()->transform(function ($anime) {
            return $this->transformAnime($anime);
        });

        return $animes;
    }
    
    // Route: GET /api/anime/search
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        $animes = Anime::where('title', 'LIKE', "%{$query}%")
                    ->with(['seasons', 'genres'])
                    ->take(20)
                    ->get();

        // ✅ FIX: Search Result ကိုလည်း transformAnime ဖြင့် ပုံစံချပေးရပါမယ်
        // Paginator မဟုတ်ဘဲ Collection ဖြစ်လို့ map() ကို သုံးပါတယ်
        $formattedData = $animes->map(function ($anime) {
            return $this->transformAnime($anime);
        });
    
        return response()->json($formattedData);
    }
    
    // GET /anime/{slug}
    public function showBySlug($slug)
    {
        // Single item ဖြစ်တဲ့အတွက် Pagination မလိုပါ
        $anime = Anime::where('slug', $slug)
                    ->with(['seasons.episodes', 'genres'])
                    ->firstOrFail();
                    
        return $this->transformAnime($anime);
    }
}