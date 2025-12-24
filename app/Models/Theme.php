<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name', 'is_active', 
        'primary_color', 'accent_color', 
        'bg_gradient_top', 'bg_gradient_bottom', 
        'enable_snow', 'greeting_text', 'icon_url'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enable_snow' => 'boolean',
    ];

    // 🔥 တစ်ခု Active ဖြစ်ရင် ကျန်တာတွေ Inactive ဖြစ်သွားအောင်လုပ်မည့် Logic
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($theme) {
            if ($theme->is_active) {
                // လက်ရှိ Save လုပ်မယ့်ကောင်က Active ဖြစ်နေရင် ကျန်တာအကုန် False ပြောင်း
                static::where('id', '!=', $theme->id)->update(['is_active' => false]);
            }
        });
    }
}