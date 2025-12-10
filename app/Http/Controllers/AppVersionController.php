<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppVersion; // Model ကို Import လုပ်ရန် မမေ့ပါနှင့်

class AppVersionController extends Controller
{
    public function checkVersion()
    {
        // Active ဖြစ်ပြီး Platform တူတာကို ရှာမယ် (Android default)
        // id desc နဲ့ ယူတာမို့ နောက်ဆုံးထည့်တဲ့ version ကို ရပါမယ်
        $latestVersion = AppVersion::where('is_active', true)
                                    ->where('platform', 'android') 
                                    ->orderBy('id', 'desc')
                                    ->first();

        if (!$latestVersion) {
            return response()->json([
                'status' => false,
                'message' => 'No version found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'latest_version' => $latestVersion->version_code,
                'force_update' => (bool) $latestVersion->force_update,
                'download_url' => $latestVersion->download_url,
                'message' => $latestVersion->message,
            ]
        ]);
    }
}