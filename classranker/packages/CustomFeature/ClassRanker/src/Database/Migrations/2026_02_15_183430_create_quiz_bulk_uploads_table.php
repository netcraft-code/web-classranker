<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_bulk_uploads', function (Blueprint $table) {
            $table->id();
            
            // File Info
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_type'); // pdf, csv
            
            // Status Tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('total_questions')->default(0);
            $table->integer('processed_questions')->default(0);
            $table->integer('failed_questions')->default(0);
            
            // Metadata
            $table->json('chapters')->nullable(); // Selected chapters
            $table->json('parsed_data')->nullable(); // Preview data
            $table->json('errors')->nullable(); // Error logs
            
            // Admin
            $table->unsignedInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('admins')->onDelete('cascade');
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_bulk_uploads');
    }
};