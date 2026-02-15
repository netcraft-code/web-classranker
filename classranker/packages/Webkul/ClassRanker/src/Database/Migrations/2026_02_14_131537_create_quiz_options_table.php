<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->onDelete('cascade');
            
            // Option Data
            $table->text('option_text'); // Changed to TEXT for rich content
            $table->boolean('is_correct')->default(0);
            $table->boolean('use_tinymce')->default(0); // Track if TinyMCE was used
            
            // Order for sorting (a, b, c, d)
            $table->integer('option_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['quiz_question_id', 'option_order']);
            $table->index('is_correct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
    }
};