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
        Schema::create('fundings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->morphs('fundable');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->enum('status', ['pending', 'cancelled', 'completed']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fundable_id', 'fundable_type', 'status']);
            $table->index(['user_id']);
            $table->index(['amount']);
            $table->index(['currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundings');
    }
};
