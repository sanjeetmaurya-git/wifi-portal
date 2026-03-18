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
            // $table->string('name')->after('id');
            // $table->decimal('price', 8, 2)->default(0)->after('name');
            // $table->integer('duration_minutes')->after('price');
            // $table->string('upload_limit')->nullable()->after('duration_minutes');
            // $table->string('download_limit')->nullable()->after('upload_limit');
            // $table->boolean('is_active')->default(true)->after('download_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifi_plans', function (Blueprint $table) {
            $table->dropColumn(['name', 'price', 'duration_minutes', 'upload_limit', 'download_limit', 'is_active']);
        });
    }
};
