<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    // ✅ anime_id ကိုပါ ထည့်သွင်းပေးရပါမယ်
    protected $fillable = [
        'user_id', 
        'anime_id', 
        'episode_id'
    ];

    // Episode အချက်အလက်ယူရန်
    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    // ✅ Anime အချက်အလက် (ပုံ၊ ခေါင်းစဉ်) ယူရန် ဒီ Function လိုပါတယ်
    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }
}