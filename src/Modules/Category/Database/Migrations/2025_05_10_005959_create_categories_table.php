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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['income', 'expense']);
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('entity_types')->nullable(); // ['investment', 'user', 'payment']
            $table->json('operations')->nullable();   // ['create', 'update', 'delete']
            $table->json('metadata')->nullable();     // Additional category metadata
            $table->timestamps();

            $table->index(['name']);
            $table->index(['type', 'status']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
