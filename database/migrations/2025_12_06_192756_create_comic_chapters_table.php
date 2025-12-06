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
        Schema::create('comic_chapters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
    $table->string('title'); // e.g., "Chapter 1"
    $table->integer('chapter_number'); // Sorting အတွက်
    
    // 🖼️ စာမျက်နှာပုံများကို Array (JSON) အနေနဲ့ သိမ်းမယ်
    $table->json('pages')->nullable(); 
    
    // Premium စနစ်အတွက်
    $table->boolean('is_premium')->default(false);
    $table->integer('coin_price')->default(0);
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comic_chapters');
    }
};
