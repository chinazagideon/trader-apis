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
        Schema::create('payment_processors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->onDelete('cascade');
            $table->foreignId('payment_id')->unique()->constrained('payments')->onDelete('cascade');
            $table->decimal('amount', 20, 2);
            $table->decimal('fee', 20, 2)->nullable();
            $table->decimal('total_amount', 20, 2)->nullable();
            $table->decimal('market_rate', 20, 2)->nullable();
            $table->decimal('fiat_amount', 20, 2)->nullable();
            $table->string('fiat_currency')->nullable();
            $table->string('currency')->nullable();
            $table->enum('status', PaymentStatusEnum::cases())->default(PaymentStatusEnum::PENDING);
            $table->json('processor_data')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_processors');
    }
};
