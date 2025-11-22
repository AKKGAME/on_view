<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User ဖန်တီးခြင်း
        User::create([
            'name' => 'Admin',
            'phone' => '09123456789', // Admin ဖုန်းနံပါတ်
            'password' => Hash::make('password'), // Password
            'coins' => 999999, // Coin အများကြီးပေးထားမယ်
            'xp' => 1000,
            'rank' => 'Game Master',
        ]);
    }
}

// database/seeders/DatabaseSeeder.php ထဲမှာ run function ထဲထည့်ပါ

\App\Models\SystemSetting::create([
    'key' => 'referral_bonus_referrer',
    'value' => '500',
    'label' => 'Bonus for Inviter (မိတ်ဆက်သူရမည့် Coin)',
]);

\App\Models\SystemSetting::create([
    'key' => 'referral_bonus_referee',
    'value' => '200',
    'label' => 'Bonus for New User (လူသစ်ရမည့် Coin)',
]);