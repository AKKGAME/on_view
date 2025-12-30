<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    use HasFactory;

    // 1. Mass Assignment ခွင့်ပြုမည့် Fields များ
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'author',
        'is_finished',
        'channel_id', // Channel ချိတ်ဆက်ရန်
        'view_count', // ကြည့်ရှုသူအရေအတွက်
    ];

    // 2. Data Type ပြောင်းလဲခြင်း
    protected $casts = [
        'is_finished' => 'boolean',
        'view_count' => 'integer',
    ];

    // 3. API Response တွင် အလိုအလျောက် ထည့်သွင်းမည့် Custom Attribute
    protected $appends = ['full_cover_url'];

    // ----------------------------------------------------------------
    // Relationships (ဆက်နွယ်မှုများ)
    // ----------------------------------------------------------------

    /**
     * Comic တစ်ခုတွင် Chapter များစွာ ရှိနိုင်သည်။
     */
    public function chapters(): HasMany
    {
        // Chapter နံပါတ်အလိုက် ငယ်စဉ်ကြီးလိုက် စီထားပါမည်
        return $this->hasMany(ComicChapter::class)->orderBy('chapter_number', 'asc');
    }

    /**
     * Channel (Translator/Group) နှင့် ချိတ်ဆက်ခြင်း
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    // ----------------------------------------------------------------
    // Accessors (Getters)
    // ----------------------------------------------------------------

    /**
     * Cover Image ၏ Full URL ကို ရယူရန် Accessor
     * (Flutter App တွင် တိုက်ရိုက်သုံးနိုင်ရန်)
     * Usage: $comic->full_cover_url
     */
    public function getFullCoverUrlAttribute()
    {
        if (!$this->cover_image) {
            return null; // Placeholder URL ထည့်လိုက ဤနေရာတွင် ပြင်ပါ
        }

        // အကယ်၍ URL အပြည့်အစုံ (http...) ပါပြီးသားဖြစ်နေရင် (ဥပမာ S3 link)
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        // Local Storage မှ ပုံဖြစ်ပါက Full URL ပြန်ပေးမည်
        return asset('storage/' . $this->cover_image);
    }
}