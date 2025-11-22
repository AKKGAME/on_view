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
    Schema::table('users', function (Blueprint $table) {
        $table->date('last_check_in')->nullable(); // နောက်ဆုံးထုတ်ယူခဲ့တဲ့ရက်
        $table->integer('check_in_streak')->default(0); // ဘယ်နှရက်ဆက်တိုက်လဲ
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
