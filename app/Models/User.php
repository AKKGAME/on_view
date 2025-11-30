<?php

namespace App\Models;

// 1. IMPORT SANCTUM (အရေးကြီးဆုံး အပိုင်း)
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\Anime;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    
    // 2. USE TRAIT (ဒီနေရာမှာ HasApiTokens မပါရင် createToken() error တက်ပါတယ်)
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
        'referral_code', // လိုရမယ်ရ ထည့်ပေးထားပါတယ် (Booted function က ထည့်ပေးမှာဖြစ်ပေမယ့်)
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_check_in' => 'date',
        ];
    }
    
    // Transaction တွေနဲ့ ချိတ်ဆက်ထားခြင်း
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // User မဆောက်ခင် Referral Code အရင်ထုတ်မယ်
            // Database မှာ referral_code column မရှိရင် Error တက်နိုင်ပါတယ် (စစ်ဆေးပါ)
            $user->referral_code = strtoupper(Str::random(8)); 
        });
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