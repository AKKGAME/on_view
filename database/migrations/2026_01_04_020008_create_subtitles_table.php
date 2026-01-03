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
    Schema::create('subtitles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('episode_id')->constrained('episodes')->cascadeOnDelete();
        $table->string('language')->default('Myanmar'); // e.g., Myanmar, English
        $table->string('url'); // Subtitle Link
        $table->string('format')->default('vtt'); // vtt or srt
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtitles');
    }
};
