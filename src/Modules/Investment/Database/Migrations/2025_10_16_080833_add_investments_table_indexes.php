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
        Schema::table("investments", function (Blueprint $table) {
            $table->index(['user_id']);
            $table->index(['pricing_id']);
            $table->index(['amount']);
            $table->index(['status']);
            $table->index(['start_date']);
            $table->index(['end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function(Blueprint $table){
            $table->dropForeign(['user_id']);
            $table->dropForeign(['pricing_id']);
            $table->dropIndex(['amount']);
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
        });
    }
};
