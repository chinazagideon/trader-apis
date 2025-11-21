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
        Schema::table('client_secrets', function (Blueprint $table) {
            $table->string('action')->after('module_name');
            // Add composite index for efficient queries
            $table->index(['client_id', 'module_name', 'action', 'is_active'], 'secrets_client_module_action_active_index');

            // Add unique constraint: one secret per client per module per action
            $table->unique(['client_id', 'module_name', 'action'], 'secrets_client_module_action_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_secrets', function (Blueprint $table) {
            $table->dropUnique('secrets_client_module_action_unique');
            $table->dropIndex('secrets_client_module_action_active_index');
            $table->dropColumn('action');
        });
    }
};
