<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Funding\Enums\FundingType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fundings', function (Blueprint $table) {
            $table->enum('type', FundingType::cases())->after('status');
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundings', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
