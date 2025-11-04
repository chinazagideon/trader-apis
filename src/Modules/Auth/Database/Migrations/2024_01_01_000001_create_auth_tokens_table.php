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
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // Create user_id column first without FK constraint
            $table->unsignedBigInteger('user_id');
            $table->enum('token_type', ['access', 'refresh', 'api', 'sanctum'])->default('access');
            $table->string('token_hash', 255)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->json('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'token_type']);
            $table->index(['token_type', 'expires_at']);
            $table->index(['expires_at']);
        });

        // Add foreign key constraint only if users table exists
        if (Schema::hasTable('users')) {
            Schema::table('auth_tokens', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
    }
};
