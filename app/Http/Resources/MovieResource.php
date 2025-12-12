<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            
            // Image URLs (Full Path ပြန်ပေးမယ်)
            'thumbnail_url' => $this->thumbnail_url,
            'cover_url' => $this->cover_url,
            
            // Movie Data
            'video_url' => $this->video_url,
            'duration' => $this->duration, // minutes
            'release_date' => $this->release_date,
            
            // Monetization
            'is_premium' => (bool) $this->is_premium,
            'coin_price' => $this->coin_price,
            'is_unlocked' => $isUnlocked,
            
            // Genres (List အနေနဲ့ ပြန်မယ်)
            'genres' => $this->genres->map(fn($g) => $g->name),
            
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}