<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class CacheManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server'; // Icon ကို စိတ်ကြိုက်ပြောင်းနိုင်သည်

    protected static ?string $navigationLabel = 'System Cache';

    protected static ?string $navigationGroup = 'System Management'; // Sidebar Group အမည်

    protected static string $view = 'filament.pages.cache-manager';

    // Optimize Clear (All in one)
    public function clearAllCache()
    {
        try {
            Artisan::call('optimize:clear');
            
            Notification::make()
                ->title('System optimized & all caches cleared!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to clear cache')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Clear Route Cache
    public function clearRoute()
    {
        Artisan::call('route:clear');
        Notification::make()->title('Route cache cleared!')->success()->send();
    }

    // Clear Config Cache
    public function clearConfig()
    {
        Artisan::call('config:clear');
        Notification::make()->title('Config cache cleared!')->success()->send();
    }

    // Clear View Cache
    public function clearView()
    {
        Artisan::call('view:clear');
        Notification::make()->title('View cache cleared!')->success()->send();
    }
    
    // Cache Everything (For Production Performance)
    public function cacheEverything()
    {
        try {
            Artisan::call('optimize');
            Notification::make()->title('System caches rebuilt successfully!')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Optimization failed')->body($e->getMessage())->danger()->send();
        }
    }
}