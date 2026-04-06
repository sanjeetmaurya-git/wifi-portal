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
        Schema::table('wifi_users', function (Blueprint $col) {
            $col->string('full_name')->nullable();
            $col->text('address')->nullable();
            $col->string('city')->nullable();
            $col->string('district')->nullable();
            $col->string('state')->nullable();
            $col->string('pincode')->nullable();
            $col->string('id_proof')->nullable(); // Aadhar, PAN, Voter card number, etc.
            $col->timestamp('last_verified_at')->nullable(); // For 15-day logic
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifi_users', function (Blueprint $col) {
            $col->dropColumn(['full_name', 'address', 'city', 'district', 'state', 'pincode', 'id_proof', 'last_verified_at']);
        });
    }
};
