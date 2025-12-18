<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_published' => 'boolean',
        'release_date' => 'date',
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    // ✅ FIX: Widget က users() လို့လှမ်းခေါ်ထားလို့ ဒီ Function နာမည်ကို users လို့ ပေးရပါမယ်
    public function users()
    {
        return $this->belongsToMany(User::class, 'movie_user')
                    ->withPivot('price') // Pivot table ထဲက ဈေးနှုန်းကိုပါ ယူမယ်
                    ->withTimestamps();
    }
}