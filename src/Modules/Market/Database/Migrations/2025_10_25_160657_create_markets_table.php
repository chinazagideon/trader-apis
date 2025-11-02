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
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('symbol')->unique();
            $table->string('image')->nullable();
            $table->string('url')->nullable();
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('type', ['spot', 'future', 'option'])->default('spot');
            $table->string('category')->default('crypto');
            $table->string('subcategory')->nullable();
            $table->timestamps();

            $table->index(['name', 'slug', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
