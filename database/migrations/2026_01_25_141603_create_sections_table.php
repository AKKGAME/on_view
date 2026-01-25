<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // ဥပမာ - "Trending Now"
        $table->string('type')->default('list'); // 'slider', 'list', 'grid' (UI ပုံစံခွဲဖို့)
        $table->integer('sort_order')->default(0); // အပေါ်အောက် စီဖို့
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    // Many-to-Many Pivot Table
    Schema::create('anime_section', function (Blueprint $table) {
        $table->id();
        $table->foreignId('section_id')->constrained()->cascadeOnDelete();
        $table->foreignId('anime_id')->constrained()->cascadeOnDelete();
        $table->integer('sort_order')->default(0); // Section ထဲမှာ ဘယ်ကားအရင်ပြမလဲစီဖို့
    });
}
};
