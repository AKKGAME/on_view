<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::create([
            'name' => 'KBZ Pay',
            'slug' => 'kpay',
            'account_name' => 'On View Official',
            'account_number' => '09-123 456 789',
            'color_class' => 'blue', // Tailwind blue color
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Wave Pay',
            'slug' => 'wave',
            'account_name' => 'On View Official',
            'account_number' => '09-987 654 321',
            'color_class' => 'yellow', // Tailwind yellow color
            'is_active' => true,
        ]);
    }
}