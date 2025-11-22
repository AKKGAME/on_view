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
    Schema::create('custom_ads', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('video_path'); // Video ဖိုင်လမ်းကြောင်း
        $table->integer('duration')->default(15); // ကြာချိန် (စက္ကန့်)
        $table->integer('reward')->default(20); // ရမယ့် Coin
        $table->boolean('is_active')->default(true); // ဖွင့်/ပိတ်
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_ads');
    }
};
