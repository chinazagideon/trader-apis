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
        Schema::create('swap_rate_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('from_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->foreignId('to_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('rate', 15, 8);
            $table->decimal('spread', 8, 4)->default(0); // Exchange spread
            $table->string('source')->default('internal'); // internal, external_api, manual
            $table->json('metadata')->nullable(); // API response, calculation details
            $table->timestamps();

            // Indexes for rate lookups
            $table->index(['from_currency_id', 'to_currency_id', 'created_at'], 'srh_currency_pair_time_idx');
            $table->index(['from_currency_id', 'to_currency_id', 'source'], 'srh_currency_pair_source_idx');
            $table->index(['created_at'], 'srh_created_at_idx');

            // Ensure we don't have duplicate rates for same currency pair at same time
            $table->unique(['from_currency_id', 'to_currency_id', 'created_at'], 'srh_unique_currency_pair_time');
            $table->unique(['from_currency_id', 'to_currency_id', 'source'], 'srh_unique_currency_pair_source');
            $table->unique(['created_at'], 'srh_unique_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swap_rate_histories');
    }
};
