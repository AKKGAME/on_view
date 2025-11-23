<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            // Overview (ဇာတ်လမ်းအကျဉ်း)
            if (!Schema::hasColumn('episodes', 'overview')) {
                $table->text('overview')->nullable()->after('title');
            }
            
            // Thumbnail URL (အပိုင်းလိုက် ပုံ)
            if (!Schema::hasColumn('episodes', 'thumbnail_url')) {
                $table->string('thumbnail_url')->nullable()->after('overview');
            }
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn(['overview', 'thumbnail_url']);
        });
    }
};