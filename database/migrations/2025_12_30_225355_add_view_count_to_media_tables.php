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
    $tables = ['animes', 'movies', 'comics'];

    foreach ($tables as $table) {
        Schema::table($table, function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->default(0)->after('slug');
        });
    }
}

public function down(): void
{
    // Rollback လုပ်ရင် ပြန်ဖျက်ဖို့
    $tables = ['animes', 'movies', 'comics'];
    foreach ($tables as $table) {
        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
}
};
