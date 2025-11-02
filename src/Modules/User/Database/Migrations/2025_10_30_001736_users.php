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
        Schema::table('users', function (Blueprint $table) {
            $table->float('total_balance')->default(0);
            $table->float('available_balance')->default(0);
            $table->enum('user_type', ['individual', 'business'])->default('individual');
            $table->string('avatar')->nullable();
            $table->float('total_commission')->default(0);
            $table->string('referral_code')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->index(['user_type', 'referral_code', 'first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_balance');
            $table->dropColumn('available_balance');
            $table->dropColumn('user_type');
            $table->dropColumn('total_commission');
            $table->dropColumn('referral_code');
            $table->dropColumn('avatar');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }
};
