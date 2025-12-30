<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ✅ (1) ဒီနေရာမှာ Variable ကို အရင်ကြေညာပါ
        $user = $request->user('sanctum'); // Login ဝင်ထားတဲ့ User ကိုယူမယ်
        
        // User ရှိရင် ဝယ်ပြီးပြီလားစစ်မယ်၊ မရှိရင် (Guest) ဆိုရင် false ပေးမယ်
        $isUnlocked = $user ? $user->hasPurchasedMovie($this->id) : false;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            
            // Image URLs
            'thumbnail_url' => $this->thumbnail_url,
            'cover_url' => $this->cover_url,
            
            // Movie Data
            'video_url' => $this->video_url,
            'duration' => $this->duration,
            'release_date' => $this->release_date,
            
            // Monetization
            'is_premium' => (bool) $this->is_premium,
            'coin_price' => $this->coin_price,
            'view_count' => $this->view_count,

            'channel' => $this->channel ? [
            'id' => $this->channel->id,
            'name' => $this->channel->name,
            'logo' => $this->channel->logo, // Full URL logic in App or Accessor
            ] : null,
            
            // ✅ (2) ကြေညာထားတဲ့ Variable ကို ဒီမှာပြန်သုံးပါ
            'is_unlocked' => $isUnlocked, 
            
            // Genres
            'genres' => $this->genres->map(fn($g) => $g->name),
            
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}