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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('api_key')->unique();
            $table->string('api_secret')->nullable();
            $table->json('config')->nullable();   // e.g., theme, branding, urls
            $table->json('features')->nullable(); // e.g., flags/entitlements
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['slug', 'is_active', 'api_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
