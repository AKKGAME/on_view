<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class B2Setting extends Model
{
    protected $fillable = [
        'b2_access_key', 'b2_secret_key', 'b2_default_region', 'b2_bucket', 'b2_endpoint'
    ];
    
    public $timestamps = true; 

    public static function getCached()
    {
        return Cache::rememberForever('b2.config', function () {
            return self::find(1); 
        });
    }

    protected static function booted()
    {
        static::saved(fn() => Cache::forget('b2.config'));
    }
}