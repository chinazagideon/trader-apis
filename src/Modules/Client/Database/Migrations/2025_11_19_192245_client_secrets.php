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
        Schema::create('client_secrets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->enum('module_name', ['notification', 'withdrawal', 'payment', 'funding', 'transfer']);
            $table->json('secrets')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('module_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_secrets');
    }
};
