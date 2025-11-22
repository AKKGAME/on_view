<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAd extends Model
{
    use HasFactory;

    // ဒီအပိုင်း လိုနေလို့ Error တက်တာပါ
    protected $fillable = [
        'title',
        'video_path',
        'duration',
        'reward',
        'is_active',
    ];
}