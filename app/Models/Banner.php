<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // ✅ Storage Facade ကို အသုံးပြုရန်

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'link_url',
        'order',
        'is_active',
        'start_date',
        'end_date',
    ];

    // Date fields တွေကို Carbon Object အဖြစ် အလိုအလျောက် ပြောင်းလဲရန်
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // ----------------------------------------------------
    // ✅ NEW: Accessor for Image Full URL (API အတွက်)
    // ----------------------------------------------------
    
    /**
     * Get the full public URL for the banner image.
     * @return string|null
     */
    public function getFullImageUrlAttribute(): ?string
    {
        // image_url မရှိရင် null ပြန်ပေးမည်
        if (!$this->image_url) {
            return null;
        }
        
        // AWS S3 သို့မဟုတ် တခြား External Storage များကို စစ်ဆေးသည်
        if (str_starts_with($this->image_url, 'http')) {
            return $this->image_url;
        }

        // Local Storage Path ကို Public URL သို့ ပြောင်းလဲသည်
        return Storage::url($this->image_url);
    }

    // 💡 Note: API Response မှာ 'full_image_url' ကို အလိုအလျောက် ထည့်သွင်းဖို့
    // $appends ကို ထည့်သွင်းရန် လိုအပ်သည် (Banner Resource ကို တိုက်ရိုက် မသုံးပါက)
    protected $appends = ['full_image_url'];

    // ----------------------------------------------------
    // End of Accessor
    // ----------------------------------------------------
}