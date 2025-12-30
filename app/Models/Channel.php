<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'logo', 
        'telegram_url', 
        'facebook_url', 
        'website_url', 
        'is_active'
    ];

    // Data Type ပြောင်းလဲသတ်မှတ်ခြင်း
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // JSON Response တွင် ထည့်သွင်းမည့် Custom Field
    protected $appends = ['full_logo_url'];

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    // Logo URL အပြည့်ရဖို့ (Flutter အတွက်)
    public function getFullLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null; // သို့မဟုတ် Default Placeholder ပုံ URL ထည့်နိုင်သည်
        }

        // အကယ်၍ http နဲ့စသော External Link ဖြစ်နေရင် အတိုင်းသားပြန်ပေးမယ်
        if (str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }

        // Local Storage Link ဖြစ်ရင် Full Domain နဲ့ တွဲပေးမယ်
        return asset('storage/' . $this->logo);
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function animes(): HasMany
    {
        return $this->hasMany(Anime::class);
    }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }

    public function comics(): HasMany
    {
        return $this->hasMany(Comic::class);
    }
}