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
            $table->integer('data_limit_mb')->nullable(); // 1000MB = 1GB
            $table->string('validity_type')->nullable(); // daily / weekly / monthly
            // $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifi_plans', function (Blueprint $table) {
            //
        });
    }
};
