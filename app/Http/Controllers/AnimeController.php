<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    
    // Helper function to format anime data
    private function formatAnimeData($animes) {
        return $animes->map(function($anime) {
            $data = $anime->toArray();
            $data['cover_url'] = $anime->cover_image;
            
            // ✅ NEW: နောက်ဆုံး Episode နံပါတ်ကို ရှာဖွေခြင်း
            // Season အားလုံးထဲက နောက်ဆုံး Season ရဲ့ နောက်ဆုံး Episode ကို ယူသည်
            $latestSeason = $anime->seasons->sortByDesc('id')->first();
            $latestEpisode = $latestSeason ? $latestSeason->episodes->sortByDesc('episode_number')->first() : null;
            
            $data['latest_episode'] = $latestEpisode ? $latestEpisode->episode_number : 0;

            return $data;
        });
    }
    
    // GET /home/latest
    public function getLatestAnimes()
    {
        // Latest 10 items for the main slider or list
        return Anime::with(['seasons', 'genres'])->latest()->take(10)->get();
    }

    // GET /home/ongoing
    public function getOngoingAnimes()
    {
        $animes = Anime::where('is_completed', false)
                    ->with(['genres', 'seasons.episodes' => function($q) {
                        $q->orderBy('episode_number', 'desc');
                    }])
                    ->latest()
                    ->take(15)
                    ->get();
                    
        return $this->formatAnimeData($animes);
    }

    // GET /anime/all
    public function getAllAnimes()
    {
        $animes = Anime::with(['seasons', 'genres'])->latest()->get();
        
        return $animes->map(function($anime) {
            $data = $anime->toArray();
            $data['cover_url'] = $anime->cover_image; 
            return $data;
        });
    }
    
    // GET /anime/{slug}
    public function showBySlug($slug)
    {
        return Anime::where('slug', $slug)->with(['seasons.episodes', 'genres'])->firstOrFail();
    }
}