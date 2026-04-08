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
        Schema::table('wifi_plans', function (Blueprint $table) {
            // Store limit in MegaBytes (MB)
            $table->integer('limit_bytes')->nullable()->after('duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifi_plans', function (Blueprint $table) {
            $table->dropColumn('limit_bytes');
        });
    }
};
