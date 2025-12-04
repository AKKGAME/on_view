<?php

// database/migrations/*_create_banners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->nullable(); // ကြော်ငြာအမည် (Internal Use)
            $table->string('image_url'); // ✅ ကြော်ငြာပုံ URL သို့မဟုတ် Path
            $table->string('link_url')->nullable(); // နှိပ်လိုက်ရင် သွားမယ့် လင့်ခ် (e.g., App Store or Website)
            
            $table->integer('order')->default(0)->index(); // ပြသရာတွင် ဦးစားပေးမှု
            $table->boolean('is_active')->default(true); // ပြသခြင်းရှိမရှိ

            // ✅ Start Date/End Date ဖြင့် ကြော်ငြာကာလကို ထိန်းချုပ်ခြင်း
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
