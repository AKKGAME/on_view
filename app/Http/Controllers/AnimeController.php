<?php

namespace App\Http\Controllers;

use App\Models\Anime;

class AnimeController extends Controller
{
    // GET /home/latest
    public function getLatestAnimes()
    {
        return Anime::with(['seasons', 'genres'])->latest()->take(10)->get();
    }

    // GET /anime/all
    public function getAllAnimes()
    {
        $animes = Anime::with(['seasons', 'genres'])->latest()->get();
        
        // cover_image ကို cover_url သို့ပြောင်းပေးသည်
        return $animes->map(function($anime) {
            $data = $anime->toArray();
            // Original code used cover_image, let's keep the naming convention clear
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