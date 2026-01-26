<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. App ကနေ ပို့လိုက်တဲ့ Key ကို စစ်မယ်
        $apiKey = $request->header('X-API-KEY');

        // 2. .env ထဲက Key နဲ့ မတူရင် (သို့) မပါရင် ပေးမဝင်ဘူး
        if ($apiKey !== env('APP_API_KEY')) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Invalid API Key.',
            ], 403);
        }

        return $next($request);
    }
}