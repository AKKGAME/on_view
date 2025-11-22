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
        $table->string('referral_code')->unique()->nullable(); // ကိုယ်ပိုင်ကုဒ်
        $table->foreignId('referrer_id')->nullable()->constrained('users'); // ဘယ်သူခေါ်တာလဲ
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
