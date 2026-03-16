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
        Schema::table('wifi_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('wifi_plan_id')->nullable()->after('user_id');
            $table->foreign('wifi_plan_id')->references('id')->on('wifi_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifi_sessions', function (Blueprint $table) {
            $table->dropForeign(['wifi_plan_id']);
            $table->dropColumn('wifi_plan_id');
        });
    }
};
