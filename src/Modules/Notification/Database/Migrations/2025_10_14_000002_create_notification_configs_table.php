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
        Schema::create('notification_configs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type'); // 'email_provider', 'sms_provider', 'push_provider', 'slack_provider', 'template'
            $table->string('name'); // 'smtp', 'ses', 'twilio', 'firebase', etc.
            $table->string('channel')->nullable(); // 'mail', 'sms', 'push', 'slack'
            $table->json('config'); // Provider credentials, endpoints, template content
            $table->integer('priority')->default(0); // For failover ordering (lower = higher priority)
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['channel', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_configs');
    }
};

