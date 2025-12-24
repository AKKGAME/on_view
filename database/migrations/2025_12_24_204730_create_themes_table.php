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
    Schema::create('themes', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Theme Name (e.g., "Christmas 2024")
        $table->boolean('is_active')->default(false); // လက်ရှိသုံးနေလား?
        
        // Colors
        $table->string('primary_color')->default('#8B5CF6');
        $table->string('accent_color')->default('#F472B6');
        $table->string('bg_gradient_top')->default('#151520');
        $table->string('bg_gradient_bottom')->default('#0A0A0A');
        
        // Effects & Content
        $table->boolean('enable_snow')->default(false);
        $table->string('greeting_text')->nullable();
        $table->string('icon_url')->nullable(); // App Bar Icon
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
