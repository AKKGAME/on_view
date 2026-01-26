<?php

namespace App\Services;

class BunnyStream
{
    public static function signUrl($path, $expirationMinutes = 60)
    {
        $securityKey = env('BUNNY_SECURITY_KEY');
        $domain = env('BUNNY_CDN_DOMAIN');
        
        // Domain နောက်က Slash ပါနေရင် ဖြုတ်မယ်
        $domain = rtrim($domain, '/');

        // Path ရှေ့မှာ Slash မပါရင် ထည့်မယ် (e.g. "videos/ep1.mp4" -> "/videos/ep1.mp4")
        $path = '/' . ltrim($path, '/');
        
        // သက်တမ်းကုန်မယ့်အချိန် (Unix Timestamp)
        $expires = time() + ($expirationMinutes * 300);

        // BunnyCDN Token Calculation (SHA256)
        // Formula: SHA256(SecurityKey + Path + Expires)
        $hashableBase = $securityKey . $path . $expires;
        
        // Token ထုတ်ခြင်း (Binary to Base64 Url Safe)
        $token = hash('sha256', $hashableBase, true);
        $token = base64_encode($token);
        $token = strtr($token, '+/', '-_');
        $token = str_replace('=', '', $token);

        // URL အပြည့်အစုံ ပြန်ထုတ်ပေးခြင်း
        return "{$domain}{$path}?token={$token}&expires={$expires}";
    }
}