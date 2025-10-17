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
        Schema::create('swap_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('swap_id')->constrained('swaps')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->enum('type', ['debit', 'credit', 'fee'])->default('debit');
            $table->timestamps();

            $table->index(['swap_id', 'type'], 'st_swap_type_idx');
            $table->index(['transaction_id'], 'st_transaction_idx');
            $table->unique(['swap_id', 'transaction_id', 'type'], 'st_unique_swap_transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swap_transactions');
    }
};
