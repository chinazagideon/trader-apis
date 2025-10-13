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
        //DROP COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
        //ADD COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->index(['method', 'amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //ADD COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->string('currency')->nullable();
        });

        //DROP COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('currency_id');
            $table->dropIndex(['method', 'amount']);
        });
    }
};
