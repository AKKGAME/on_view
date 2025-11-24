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
    Schema::create('seasons', function (Blueprint $table) {
        $table->id();
        $table->foreignId('anime_id')->constrained()->cascadeOnDelete(); // ဘယ် Anime ရဲ့ Season လဲ
        $table->string('title'); // ဥပမာ - "Season 1" (သို့) "Swordsmith Village Arc"
        $table->integer('season_number')->default(1); // စီရလွယ်အောင် (1, 2, 3)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
