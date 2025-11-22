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
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique(); // e.g., 'referral_reward_referrer'
        $table->string('value'); // e.g., '500'
        $table->string('label'); // Admin မှာပြမယ့် နာမည်
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
