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
    Schema::create('movies', function (Blueprint $table) {
        $table->id();
        $table->string('tmdb_id')->nullable();
        $table->string('title');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        
        // Media
        $table->string('thumbnail_url')->nullable();
        $table->string('cover_url')->nullable();
        $table->text('video_url')->nullable(); // Movie Link (Direct/Iframe)
        
        // Details
        $table->integer('duration')->nullable(); // Minutes
        $table->date('release_date')->nullable();
        
        // Monetization (Movie တစ်ကားလုံးကို ဝယ်ကြည့်ရမယ်ဆိုရင်)
        $table->boolean('is_premium')->default(false);
        $table->integer('coin_price')->default(0);
        $table->integer('xp_reward')->default(10);
        
        // Status
        $table->boolean('is_published')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
