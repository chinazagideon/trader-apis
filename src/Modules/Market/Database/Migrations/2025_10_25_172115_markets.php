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
        Schema::table('market_prices', function (Blueprint $table) {
            $table->decimal('price', 20, 2)->change();
            $table->decimal('market_cap', 20, 2)->change();
            $table->decimal('total_supply', 20, 2)->change();
            $table->decimal('max_supply', 20, 2)->change();
            $table->decimal('circulating_supply', 20, 2)->change();
            $table->decimal('total_volume', 20, 2)->change();
            $table->decimal('total_volume_24h', 20, 2)->change();
            $table->decimal('total_volume_7d', 20, 2)->change();
            $table->decimal('total_volume_30d', 20, 2)->change();
            $table->decimal('total_volume_90d', 20, 2)->change();
            $table->decimal('total_volume_180d', 20, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_prices', function (Blueprint $table) {
            // Revert back to smaller precision
            $table->decimal('price', 10, 2)->change();
            $table->decimal('market_cap', 10, 2)->change();
            $table->decimal('total_supply', 10, 2)->change();
            $table->decimal('max_supply', 10, 2)->change();
            $table->decimal('circulating_supply', 10, 2)->change();
            $table->decimal('total_volume', 10, 2)->change();
            $table->decimal('total_volume_24h', 10, 2)->change();
            $table->decimal('total_volume_7d', 10, 2)->change();
            $table->decimal('total_volume_30d', 10, 2)->change();
            $table->decimal('total_volume_90d', 20, 2)->change();
            $table->decimal('total_volume_180d', 20, 2)->change();
        });
    }
};
