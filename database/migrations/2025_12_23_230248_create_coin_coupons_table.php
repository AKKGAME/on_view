<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ၁. ကူပွန်အချက်အလက်များ သိမ်းမည့် Table
        Schema::create('coin_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ကုဒ် (ဥပမာ - GIFT100)
            $table->integer('coin_amount');   // ရမည့် coin ပမာဏ
            $table->integer('usage_limit')->nullable(); // စုစုပေါင်း ဘယ်နှကြိမ်သုံးခွင့်ရှိလဲ (Null ဆိုရင် အကန့်အသတ်မရှိ)
            $table->integer('used_count')->default(0);  // ဘယ်နှကြိမ်သုံးပြီးပြီလဲ
            $table->dateTime('expires_at')->nullable(); // သက်တမ်းကုန်ဆုံးရက်
            $table->boolean('is_active')->default(true); // ဖွင့်/ပိတ်
            $table->timestamps();
        });

        // ၂. User ဘယ်ကူပွန်သုံးပြီးပြီလဲ မှတ်တမ်းတင်မည့် Table
        Schema::create('coin_coupon_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coin_coupon_id')->constrained('coin_coupons')->cascadeOnDelete();
            $table->timestamp('redeemed_at'); // သုံးလိုက်တဲ့အချိန်

            // User တစ်ယောက် ကူပွန်တစ်ခုကို တစ်ကြိမ်ပဲသုံးလို့ရအောင် တားမယ်
            $table->unique(['user_id', 'coin_coupon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_coupon_user');
        Schema::dropIfExists('coin_coupons');
    }
};