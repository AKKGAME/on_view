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
    // Anime Table
    Schema::table('animes', function (Blueprint $table) {
        $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
    });

    // Movie Table
    Schema::table('movies', function (Blueprint $table) {
        $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
    });

    // Comic Table
    Schema::table('comics', function (Blueprint $table) {
        $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            //
        });
    }
};
