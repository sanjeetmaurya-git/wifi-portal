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
        Schema::create('mikrotik_commands', function (Blueprint $table) {
            $table->id();
            $table->string('router_id')->nullable()->index();
            $table->text('command');
            $table->enum('status', ['pending', 'executed', 'failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('executed_at')->nullable();
            $table->text('error_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_commands');
    }
};
