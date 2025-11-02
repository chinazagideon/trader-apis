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
        Schema::table('fundings', function (Blueprint $table) {
            $table->decimal('fiat_amount', 10, 2)->nullable()->after('amount');
            $table->foreignId('fiat_currency_id')->default(0);
            $table->index(['fiat_currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundings', function (Blueprint $table) {
            $table->dropColumn('fiat_amount');
            $table->dropForeign(['fiat_currency_id']);
            $table->dropColumn('fiat_currency_id');
        });
    }
};
