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
        Schema::create('question_assignments', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('board_id')->constrained('boards')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate assignments
            $table->unique([
                'question_id', 
                'board_id', 
                'grade_id', 
                'subject_id', 
                'book_id', 
                'chapter_id'
            ], 'unique_question_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_assignments');
    }
};