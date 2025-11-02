<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    function types(): array
    {
        return [
            'real-estate',
            'forex',
            'crypto',
            'commodities',
            'bonds',
            'metaverse',
            'mining',
            'fund',
            'nft',
            'agriculture',
            'oil-gas'
        ];
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->enum('type', $this->types())->nullable()->after('name');
            $table->enum('risk', ['low', 'medium', 'high'])->nullable()->after('type');

            $table->index(['type', 'risk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('type');
            $table->dropColumn('risk');
        });
    }
};
