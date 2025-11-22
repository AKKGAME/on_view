<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('payment_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        $table->string('payment_method'); // Kpay, Wave, etc.
        $table->decimal('amount', 10, 0); // Coin ပမာဏ (ဥပမာ - 1000, 5000)
        $table->string('phone_last_digits')->nullable(); // ငွေလွှဲသူ ဖုန်းနံပါတ် (နောက်ဆုံးဂဏန်း)
        $table->string('screenshot_path'); // ငွေလွှဲစလစ် ပုံ
        
        $table->string('status')->default('pending'); // pending, approved, rejected
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
