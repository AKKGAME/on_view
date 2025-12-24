<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function getActiveTheme()
    {
        // Active ဖြစ်နေတာကိုရှာမယ်၊ မရှိရင် နောက်ဆုံးတစ်ခု (သို့) default ပြန်ပေးမယ်
        $theme = Theme::where('is_active', true)->first();

        // Backup Default Theme (Database မှာ ဘာမှမရှိရင်)
        if (!$theme) {
            $theme = [
                'mode' => 'default',
                'primary_color' => '#8B5CF6',
                'accent_color' => '#F472B6',
                'bg_gradient_top' => '#151520',
                'bg_gradient_bottom' => '#0A0A0A',
                'enable_snow' => false,
                'greeting_text' => 'Anime Gabar',
                'icon_url' => null,
            ];
        } else {
            // Icon URL ကို Full Path ပြောင်းပေးမယ် (Filament က path ပဲသိမ်းလို့)
            if ($theme->icon_url) {
                $theme->icon_url = asset('storage/' . $theme->icon_url);
            }
            // App ဘက်က 'mode' field လိုချင်ရင် name ကို mode အဖြစ်သုံးလိုက်မယ်
            $theme->mode = $theme->name; 
        }

        return response()->json([
            'status' => true,
            'data' => $theme
        ]);
    }
}