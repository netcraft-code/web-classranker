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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');

            $table->string('title');
            $table->string('code');
            $table->string('writer')->nullable();
            $table->string('publisher')->nullable();
            $table->string('edition')->nullable();
            $table->integer('publication_year')->nullable();
            $table->integer('total_pages')->nullable();

            $table->boolean('is_premium')->default(false);
            $table->string('avatar')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();

            // Foreign key constraints (official Laravel way)
            $table->foreign('board_id')
                  ->references('id')
                  ->on('boards')
                  ->onDelete('cascade');

            $table->foreign('grade_id')
                  ->references('id')
                  ->on('grades')
                  ->onDelete('cascade');

            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['board_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['subject_id']);
        });

        Schema::dropIfExists('books');
    }
};
