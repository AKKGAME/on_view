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
    Schema::table('animes', function (Blueprint $table) {
        // id column ပြီးရင် tmdb_id ဆိုတဲ့ အကွက် အသစ်ထည့်မယ်
        $table->string('tmdb_id')->nullable()->after('id'); 
    });
}

public function down(): void
{
    Schema::table('animes', function (Blueprint $table) {
        $table->dropColumn('tmdb_id');
    });
}
};
