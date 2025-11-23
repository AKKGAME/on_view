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
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique(); // ဥပမာ - tmdb_api_key
        $table->text('value')->nullable(); // ဥပမာ - xxxxxxxxxx
        $table->string('label')->nullable(); // Admin မှာပြမယ့် နာမည် (ဥပမာ - TMDB API Key)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
