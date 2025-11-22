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
    Schema::create('animes', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Anime နာမည်
        $table->text('description')->nullable(); // အညွှန်း
        $table->string('slug')->unique(); // URL မှာသုံးဖို့ (Example: one-piece)
        $table->string('thumbnail_url')->nullable(); // ပိုစတာပုံ
        $table->string('cover_url')->nullable(); // Background အကြီးပုံ
        $table->boolean('is_completed')->default(false); // ပြီးသွားပြီလား?
        $table->integer('total_episodes')->default(0); // စုစုပေါင်း အပိုင်း
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animes');
    }
};
