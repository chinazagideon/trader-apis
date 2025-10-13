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

        //DROP VAR COLUMNS
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('reference');
        });

        //ADD ID COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('uuid')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //ADD VAR COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference');
        });

        //DROP ID COLUMN
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
