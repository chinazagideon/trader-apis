<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Payment\Database\Models\Payment;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->morphs('withdrawable');
            $table->index(['withdrawable_id', 'withdrawable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignIdFor(Payment::class, 'payment_id')->constrained()->onDelete('cascade');
            $table->dropMorphs('withdrawable');
        });
    }
};
