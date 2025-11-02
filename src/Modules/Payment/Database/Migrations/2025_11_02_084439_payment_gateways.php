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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('mode', ['live', 'test'])->default('test');
            $table->enum('type', ['crypto', 'fiat'])->default('fiat');
            $table->boolean('is_traditional')->default(false);
            $table->json('instructions')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->json('credentials')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index(['name','slug']);
            $table->index(['type','is_active']);
            $table->index(['is_traditional']);
            $table->index(['mode','is_active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
