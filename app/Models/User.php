<?php

namespace App\Models;

// 1. STANDARD IMPORTS
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\Anime;

// 2. FILAMENT IMPORTS
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

// ✅ CORRECTED IMPORT (Auth ဖြုတ်လိုက်ပါပြီ)
use Filament\Notifications\Auth\Concerns\HasDatabaseNotifications;

use Spatie\Permission\Traits\HasRoles; 

class User extends Authenticatable implements FilamentUser 
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    
    // 3. USE TRAITS
    use HasApiTokens, HasFactory, Notifiable;
    
    // use HasDatabaseNotifications; // ✅ အခုမှန်သွားပါပြီ
    use HasRoles; 

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
        'premium_expires_at', 
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

    protected $appends = [
        'is_premium',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_check_in' => 'date',
            'premium_expires_at' => 'datetime', 
        ];
    }

    // --- FILAMENT ACCESS CONTROL ---
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->id === 1 || $this->getRoleNames()->isNotEmpty();
    }
    
    // --- ACCESSORS ---
    public function getIsPremiumAttribute()
    {
        return $this->premium_expires_at && $this->premium_expires_at->isFuture();
    }

    // --- EVENTS ---
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8)); 
            }
            if (!isset($user->coins)) $user->coins = 0;
            if (!isset($user->xp)) $user->xp = 0;
            if (!isset($user->rank)) $user->rank = 'Novice';
        });
    }

    // --- RELATIONSHIPS ---
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

    public function purchasedMovies()
    {
        return $this->belongsToMany(Movie::class, 'movie_user')
                    ->withTimestamps()
                    ->withPivot('price');
    }

    public function hasPurchasedMovie($movieId)
    {
        return $this->purchasedMovies()->where('movie_id', $movieId)->exists();
    }
}