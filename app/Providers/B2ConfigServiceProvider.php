<?php

namespace App\Providers;

use App\Models\B2Setting; 
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class B2ConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole() || $this->app->isBooted()) {
            
            $settings = B2Setting::find(1); 

            if ($settings && $settings->b2_secret_key) {
                Config::set('filesystems.disks.b2.key', $settings->b2_access_key);
                Config::set('filesystems.disks.b2.secret', $settings->b2_secret_key);
                Config::set('filesystems.disks.b2.region', $settings->b2_default_region);
                Config::set('filesystems.disks.b2.bucket', $settings->b2_bucket);
                Config::set('filesystems.disks.b2.endpoint', $settings->b2_endpoint);
            } else {
                Log::warning('B2 Settings are missing from the database. Please configure via Admin Panel.');
            }
        }
    }
}