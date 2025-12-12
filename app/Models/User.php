<?php

namespace App\Models;

// 1. IMPORT SANCTUM
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\Anime;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    
    // 2. USE TRAIT
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',      
        'password',
        'coins',      
        'xp',         
        'rank',       
        'referral_code',
        'premium_expires_at', // Admin က ပြင်လို့ရအောင် fillable ထဲထည့်ထားသင့်ပါတယ်
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ✅ IMPORTANT: API Response ထဲမှာ is_premium ပါလာစေရန်
     */
    protected $appends = [
        'is_premium',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_check_in' => 'date',
            'premium_expires_at' => 'datetime', // ✅ Date အဖြစ် သတ်မှတ်
        ];
    }
    
    // --- ACCESSORS ---

    // ✅ Helper: Premium ဟုတ်မဟုတ် စစ်ရန် Logic
    public function getIsPremiumAttribute()
    {
        // Premium သက်တမ်းကုန်ဆုံးရက် ရှိပြီး၊ အနာဂတ်မှာ ဖြစ်နေရင် True ပြန်မယ်
        return $this->premium_expires_at && $this->premium_expires_at->isFuture();
    }

    // --- EVENTS ---

    protected static function booted()
    {
        static::creating(function ($user) {
            // User မဆောက်ခင် Referral Code အလိုအလျောက် ထုတ်မယ်
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8)); 
            }
            
            // Default Values (Optional)
            if (!isset($user->coins)) $user->coins = 0;
            if (!isset($user->xp)) $user->xp = 0;
            if (!isset($user->rank)) $user->rank = 'Novice';
        });
    }

    // --- RELATIONSHIPS ---

    // Transaction တွေနဲ့ ချိတ်ဆက်ထားခြင်း
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function watchlist()
    {
        return $this->belongsToMany(Anime::class, 'watchlists')
                    ->withTimestamps()
                    ->orderBy('watchlists.created_at', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}