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
    Schema::create('app_versions', function (Blueprint $table) {
        $table->id();
        $table->string('version_code'); // ဥပမာ: "1.0.2"
        $table->boolean('force_update')->default(false); // မဖြစ်မနေ Update လုပ်မလား
        $table->string('download_url'); // PlayStore မဟုတ်သော Direct Link
        $table->text('message')->nullable(); // Update အကြောင်း စာသား
        $table->string('platform')->default('android'); // Android/iOS ခွဲချင်ရင်သုံးရန်
        $table->boolean('is_active')->default(true); // လက်ရှိ Active ဖြစ်မဖြစ်
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
