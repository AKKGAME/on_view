<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Anime extends Model
{
    use HasFactory;

    // Mass Assignment အတွက် Field များကို ခွင့်ပြုခြင်း
    protected $fillable = [
        'tmdb_id',
        'title',
        'slug',
        'description',
        'thumbnail_url',
        'cover_url',
        'total_episodes',
        'is_completed',
        'channel_id', // Channel ချိတ်ဆက်ရန်
        'view_count', // ကြည့်ရှုသူအရေအတွက်
    ];

    // Data Type များကို အလိုအလျောက် ပြောင်းလဲသတ်မှတ်ခြင်း
    protected $casts = [
        'is_completed' => 'boolean',
        'total_episodes' => 'integer',
        'view_count' => 'integer',
    ];

    // Seasons များနှင့် ချိတ်ဆက်ခြင်း
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    // Genres များနှင့် ချိတ်ဆက်ခြင်း
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    // Channel (Translator/Encoder) နှင့် ချိတ်ဆက်ခြင်း
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}