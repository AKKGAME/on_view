<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    /**
     * Database Table Name (Optional but good for clarity)
     */
    protected $table = 'app_versions';

    /**
     * The attributes that are mass assignable.
     * Filament Form ကနေ Data ဖြည့်သွင်းခွင့်ပြုမယ့် Field များ
     */
    protected $fillable = [
        'version_code', // e.g., 1.0.2
        'force_update', // e.g., true/false
        'download_url', // Direct APK Link
        'message',      // Update note
        'platform',     // android or ios
        'is_active',    // true/false
    ];

    /**
     * The attributes that should be cast.
     * Database ထဲမှာ 0 နဲ့ 1 သိမ်းထားပေမယ့် PHP မှာ Boolean (true/false) အဖြစ်သုံးရန်
     */
    protected $casts = [
        'force_update' => 'boolean',
        'is_active' => 'boolean',
    ];
}