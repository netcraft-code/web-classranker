<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('duration_value')->default(1); // e.g. 1, 3, 6, 12
            $table->enum('duration_type', ['days', 'months', 'years'])->default('months');
            $table->json('features')->nullable();      // ["Feature 1", "Feature 2"]
            $table->string('avatar')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};