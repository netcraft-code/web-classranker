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
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->onDelete('cascade');
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->onDelete('cascade');
            
            // User's selected option
            $table->foreignId('quiz_option_id')->nullable()->constrained('quiz_options')->onDelete('set null');
            
            // Correct option for reference
            $table->foreignId('correct_option_id')->nullable()->constrained('quiz_options')->onDelete('set null');
            
            // Answer Data
            $table->boolean('is_correct')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['quiz_attempt_id', 'quiz_question_id']);
            $table->index('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};