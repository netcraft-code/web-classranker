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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            
            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Status
            $table->boolean('status')->default(0);
            $table->boolean('is_premium')->default(0);
            
            // Creator
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('admins')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};