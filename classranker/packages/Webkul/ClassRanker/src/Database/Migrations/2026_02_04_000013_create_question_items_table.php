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
        Schema::create('question_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            
            // Question Data
            $table->integer('question_number');
            $table->string('question_title')->nullable();
            $table->longText('question');
            $table->longText('answer');
            $table->string('page_number')->nullable();
            
            // Order for sorting
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['question_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_items');
    }
};