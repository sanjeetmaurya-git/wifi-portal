<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plan Types:
     * - 'daily'     : Daily Data Plan (e.g., 1GB/day for 30 days). MikroTik resets limit each day.
     * - 'unlimited' : Unlimited data plan, speed limited, time based.
     * - 'datapack'  : One-time top-up pack. Can be combined WITH an active daily plan.
     */
    public function up(): void
    {
        Schema::table('wifi_plans', function (Blueprint $table) {
            // Plan category
            $table->enum('plan_type', ['daily', 'unlimited', 'datapack'])
                  ->default('unlimited')
                  ->after('name');

            // For daily plans: how many MB is allowed per day (resets at midnight)
            $table->integer('daily_data_mb')->nullable()->after('limit_bytes')
                  ->comment('MB allowed per day. For daily plans only. Null = no daily cap.');

            // Description shown on the plans page
            $table->string('description')->nullable()->after('plan_type');
        });
    }

    public function down(): void
    {
        Schema::table('wifi_plans', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'daily_data_mb', 'description']);
        });
    }
};
