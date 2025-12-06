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
        Schema::create('comics', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique(); // API အတွက်
    $table->text('description')->nullable();
    $table->string('cover_image'); // မျက်နှာဖုံးပုံ
    $table->string('author')->nullable();
    $table->boolean('is_finished')->default(false); // Ongoing/Completed
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comics');
    }
};
