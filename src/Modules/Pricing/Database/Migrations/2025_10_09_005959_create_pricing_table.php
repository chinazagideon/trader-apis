<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     *
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->decimal('min_amount', 10, 2);
            $table->enum('contract', ['daily', 'weekly', 'monthly', 'yearly', 'lifetime']);
            $table->decimal('max_amount', 10, 2);
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->integer('lifespan')->nullable();
            $table->enum('type', ['trade', 'mining', 'staking']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['min_amount', 'max_amount']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
