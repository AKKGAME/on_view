<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('subscription_plans', function (Blueprint $table) {
        $table->id();
        $table->string('name');           // e.g., "1 Month VIP"
        $table->integer('coin_price');    // e.g., 5000
        $table->integer('duration_days'); // e.g., 30
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
