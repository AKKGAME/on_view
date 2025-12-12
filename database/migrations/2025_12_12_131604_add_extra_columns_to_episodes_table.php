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
        Schema::table('episodes', function (Blueprint $table) {
            // duration ကို မိနစ် (integer) အနေနဲ့သိမ်းမယ်
            $table->integer('duration')
                  ->nullable() // မထည့်လဲရအောင်
                  ->after('episode_number'); // episode_number ပြီးမှ ထားမယ်

            // air_date ကို date format နဲ့သိမ်းမယ်
            $table->date('air_date')
                  ->nullable()
                  ->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            // Rollback လုပ်ရင် ပြန်ဖျက်မယ်
            $table->dropColumn(['duration', 'air_date']);
        });
    }
};