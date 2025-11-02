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
        Schema::create('swaps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->foreignId('to_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('from_amount', 15, 8); // Increased precision for crypto
            $table->decimal('to_amount', 15, 8);
            $table->decimal('fee_amount', 15, 8);
            $table->decimal('total_amount', 15, 8);
            $table->decimal('rate', 15, 8);
            $table->enum('status', [
                'draft',           // User is still configuring
                'pending',         // Waiting for confirmation
                'confirmed',       // User confirmed, waiting for processing
                'processing',      // Currently being processed
                'completed',       // Successfully completed
                'failed',          // Processing failed
                'cancelled',       // User cancelled
                'expired'          // Expired without completion
            ])->default('draft');

            // Add missing audit fields
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Add fee breakdown
            $table->decimal('platform_fee', 15, 8)->default(0);
            $table->decimal('network_fee', 15, 8)->default(0);
            $table->decimal('exchange_fee', 15, 8)->default(0);

            // Add rate information
            $table->decimal('market_rate', 15, 8)->nullable(); // Real-time market rate
            $table->decimal('applied_rate', 15, 8)->nullable(); // Rate actually applied
            $table->timestamp('rate_updated_at')->nullable();

            // Add transaction references
            $table->string('transaction_reference')->nullable()->unique();
            $table->string('external_reference')->nullable(); // External API reference

            // Add metadata for extensibility
            $table->json('metadata')->nullable();
            $table->json('processing_log')->nullable(); // Track processing steps

            $table->text('notes')->nullable();
            $table->timestamps();

            // Add soft deletes for audit trail
            $table->softDeletes();

            // Optimized composite indexes for common query patterns
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['status', 'processed_by']);
            $table->index(['processed_at']);
            $table->index(['from_currency_id', 'to_currency_id', 'status']);
            $table->index(['from_currency_id', 'to_currency_id', 'created_at']);
            $table->index(['status', 'total_amount', 'created_at']);
            $table->index(['created_at', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['expires_at']);
            $table->index(['transaction_reference']);
            $table->index(['external_reference']);
            $table->index(['rate_updated_at']);
            $table->index(['from_currency_id', 'to_currency_id', 'rate_updated_at']);
            $table->index(['deleted_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swaps');
    }
};
