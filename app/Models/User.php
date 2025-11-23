<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\Anime;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',      // Email အစား Phone ကို ပြောင်းထည့်ထားပါတယ်
        'password',
        'coins',      // Coin စနစ်အတွက်
        'xp',         // Level စနစ်အတွက်
        'rank',       // User Rank အတွက်
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime', // Email verified အစား Phone verified ပြောင်းထားပါတယ်
            'password' => 'hashed',
            'last_check_in' => 'date',
        ];
    }
    
    // Transaction တွေနဲ့ ချိတ်ဆက်ထားခြင်း (လိုရမယ်ရ ထည့်ထားပါ)
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function booted()
{
    static::creating(function ($user) {
        // User မဆောက်ခင် Code အရင်ထုတ်မယ် (Unique ဖြစ်အောင်စစ်မယ်)
        $user->referral_code = strtoupper(Str::random(8)); 
    });
}

public function watchlist()
{
    return $this->belongsToMany(Anime::class, 'watchlists')
                ->withTimestamps()
                // created_at ရှေ့မှာ Table နာမည် 'watchlists.' ခံပေးရပါမယ်
                ->orderBy('watchlists.created_at', 'desc'); // ✅ မှန်ကန်သော ကုဒ်
}

public function comments()
{
    return $this->hasMany(Comment::class);
}

}