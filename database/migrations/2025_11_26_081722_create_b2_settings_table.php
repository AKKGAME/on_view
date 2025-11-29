<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2_settings', function (Blueprint $table) {
            $table->id();
            $table->string('b2_access_key')->nullable();
            $table->text('b2_secret_key')->nullable();
            $table->string('b2_default_region')->default('us-east-005');
            $table->string('b2_bucket')->nullable();
            $table->string('b2_endpoint')->nullable();
            $table->timestamps();
        });

        \DB::table('b2_settings')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('b2_settings');
    }
};
