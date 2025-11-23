<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Seasons Table မှာ slug ထည့်မယ်
        Schema::table('seasons', function (Blueprint $table) {
            if (!Schema::hasColumn('seasons', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
        });

        // Episodes Table မှာလည်း slug လိုအပ်လို့ တစ်ခါတည်း ထည့်မယ်
        Schema::table('episodes', function (Blueprint $table) {
            if (!Schema::hasColumn('episodes', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};