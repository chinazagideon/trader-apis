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
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('market_cap', 10, 2);
            $table->decimal('total_supply', 10, 2);
            $table->decimal('max_supply', 10, 2);
            $table->decimal('circulating_supply', 10, 2);
            $table->decimal('total_volume', 10, 2);
            $table->decimal('total_volume_24h', 10, 2);
            $table->decimal('total_volume_7d', 10, 2);
            $table->decimal('total_volume_30d', 10, 2);
            $table->decimal('total_volume_90d', 10, 2);
            $table->decimal('total_volume_180d', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};
