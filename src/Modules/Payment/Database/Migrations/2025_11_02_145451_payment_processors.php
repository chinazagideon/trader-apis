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
        Schema::table('payment_processors', function (Blueprint $table) {
            $table->string('payment_hash')->unique()->nullable();
            $table->string('payment_url')->nullable();
            $table->index(['payment_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_processors', function(Blueprint $table){
            $table->dropColumn('payment_hash');
            $table->dropColumn('payment_url');
            $table->dropIndex(['payment_hash']);
        });
    }
};
