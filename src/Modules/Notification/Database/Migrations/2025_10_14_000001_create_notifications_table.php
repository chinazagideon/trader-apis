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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // Notification class name
            $table->morphs('notifiable'); // notifiable_type, notifiable_id (polymorphic) - auto-creates index
            $table->text('data'); // JSON data for the notification
            $table->json('channels_sent')->nullable(); // Array of successfully sent channels
            $table->json('failed_channels')->nullable(); // Array of failed channels with error info
            $table->json('metadata')->nullable(); // Additional metadata (user context, etc.)
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('read_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

