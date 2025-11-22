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
    Schema::create('episodes', function (Blueprint $table) {
        $table->id();
        
        // အရင်က anime_id နေရာမှာ season_id နဲ့ ချိတ်မယ်
        $table->foreignId('season_id')->constrained()->cascadeOnDelete(); 
        
        $table->string('title'); 
        $table->integer('episode_number'); 
        $table->string('video_url'); 
        
        // Gaming Logic
        $table->boolean('is_premium')->default(false);
        $table->integer('coin_price')->default(0);
        $table->integer('xp_reward')->default(10);
        
        $table->integer('view_count')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
