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
        Schema::create('otp_requests', function (Blueprint $table) {
        $table->id();
        $table->string('mobile');
        $table->string('otp_code');
        $table->ipAddress('ip_address')->nullable();
        $table->timestamp('expires_at');
        $table->boolean('verified')->default(false);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_requests');
    }
};
