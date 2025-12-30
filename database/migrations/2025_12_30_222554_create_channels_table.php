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
    Schema::create('channels', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Channel Name
        $table->string('slug')->unique(); // URL မှာသုံးဖို့
        $table->string('logo')->nullable(); // Logo ပုံ
        $table->string('telegram_url')->nullable();
        $table->string('facebook_url')->nullable();
        $table->string('website_url')->nullable(); // တခြား Link လိုရင်
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
