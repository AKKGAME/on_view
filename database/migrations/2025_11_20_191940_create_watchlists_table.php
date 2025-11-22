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
    Schema::create('watchlists', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('anime_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
        
        // User တစ်ယောက်က Anime တစ်ကားကို ၂ ခါ ပြန်မသိမ်းမိအောင် တားမယ်
        $table->unique(['user_id', 'anime_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
