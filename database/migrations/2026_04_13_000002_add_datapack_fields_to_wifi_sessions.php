<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add datapack_session_id to wifi_sessions.
     * When a user buys a datapack on top of a daily plan, 
     * the datapack session references the parent daily session.
     */
    public function up(): void
    {
        Schema::table('wifi_sessions', function (Blueprint $table) {
            // References the active daily/unlimited plan session this pack is stacked on
            $table->unsignedBigInteger('parent_session_id')->nullable()->after('wifi_plan_id');
            $table->foreign('parent_session_id')->references('id')->on('wifi_sessions')->onDelete('set null');

            // Extra MB added by a datapack on top of daily limit
            $table->integer('bonus_data_mb')->default(0)->after('parent_session_id');

            // MB used so far in this session (synced from MikroTik)
            $table->integer('used_mb')->default(0)->after('bonus_data_mb');
        });
    }

    public function down(): void
    {
        Schema::table('wifi_sessions', function (Blueprint $table) {
            $table->dropForeign(['parent_session_id']);
            $table->dropColumn(['parent_session_id', 'bonus_data_mb', 'used_mb']);
        });
    }
};
