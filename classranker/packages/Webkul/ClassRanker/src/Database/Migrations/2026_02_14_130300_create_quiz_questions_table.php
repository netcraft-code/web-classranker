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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            
            // Question Data
            $table->text('question_text');
            
            // Order for sorting
            $table->integer('question_order')->default(0);
            
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['quiz_id', 'question_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};