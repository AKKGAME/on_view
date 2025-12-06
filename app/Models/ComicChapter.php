<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComicChapter extends Model
{
    use HasFactory;

    // 1. Mass Assignment
    protected $fillable = [
        'comic_id',
        'title',            // e.g., "Chapter 1"
        'chapter_number',   // e.g., 1, 2, 3 (Sorting အတွက်)
        'pages',            // Image Paths Array (JSON)
        'is_premium',       // Coin နဲ့ ဝယ်ရမလား?
        'coin_price',       // Price
    ];

    // 2. Data Casting
    protected $casts = [
        'is_premium' => 'boolean',
        'pages' => 'array', // ⚠️ အရေးကြီးသည်: JSON ကို PHP Array အဖြစ် အလိုအလျောက်ပြောင်းမည်
    ];

    // 3. API Response တွင် ပါဝင်မည့် Custom Field
    protected $appends = ['full_page_urls'];

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }

    // ----------------------------------------------------------------
    // Accessors (Getters)
    // ----------------------------------------------------------------

    /**
     * Pages (ပုံလမ်းကြောင်းများ) ကို Full URL List အဖြစ် ပြောင်းလဲပေးခြင်း
     * Flutter App တွင် တိုက်ရိုက်သုံးရန် (e.g., http://domain.com/storage/...)
     */
    public function getFullPageUrlsAttribute()
    {
        // ပုံမရှိရင် Empty Array ပြန်မယ်
        if (empty($this->pages)) {
            return [];
        }

        // Array ထဲက Path တစ်ခုချင်းစီကို Full URL ပြောင်းမယ်
        return collect($this->pages)->map(function ($pagePath) {
            // Already a full URL? (S3 etc.)
            if (str_starts_with($pagePath, 'http')) {
                return $pagePath;
            }
            
            // Local Storage Link
            return asset('storage/' . $pagePath);
        })->toArray();
    }
}