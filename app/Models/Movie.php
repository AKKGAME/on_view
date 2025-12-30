<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    // Mass Assignment အတွက် Field များကို ခွင့်ပြုခြင်း
    protected $fillable = [
        'tmdb_id',
        'title',
        'slug',
        'description',
        'video_url',
        'thumbnail_url',
        'cover_url',
        'duration',
        'release_date',
        'is_premium',
        'coin_price',
        'xp_reward',
        'is_published',
        'channel_id', // Channel ချိတ်ဆက်ရန်
        'view_count', // ကြည့်ရှုသူအရေအတွက်
    ];

    // Data Type များကို အလိုအလျောက် ပြောင်းလဲသတ်မှတ်ခြင်း
    protected $casts = [
        'is_premium' => 'boolean',
        'is_published' => 'boolean',
        'release_date' => 'date',
        'view_count' => 'integer',
        'coin_price' => 'integer',
        'xp_reward' => 'integer',
    ];

    // Genres များနှင့် ချိတ်ဆက်ခြင်း
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    // ဝယ်ယူထားသော User များနှင့် ချိတ်ဆက်ခြင်း
    // ✅ FIX: Widget က users() လို့လှမ်းခေါ်ထားလို့ ဒီ Function နာမည်ကို users လို့ ပေးထားခြင်းဖြစ်သည်
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'movie_user')
                    ->withPivot('price') // Pivot table ထဲက ဈေးနှုန်းကိုပါ ယူမယ်
                    ->withTimestamps();
    }

    // Channel (Translator/Encoder) နှင့် ချိတ်ဆက်ခြင်း
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}