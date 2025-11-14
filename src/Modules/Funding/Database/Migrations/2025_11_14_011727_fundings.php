<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Payment\Enums\PaymentStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fundings', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', PaymentStatusEnum::cases());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
