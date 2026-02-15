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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('book_id');

            // Chapter fields
            $table->string('title');
            $table->string('code');
            $table->boolean('is_premium')->default(false);
            $table->string('avatar')->nullable();
            $table->boolean('status')->default(false);

            $table->timestamps();

            // Foreign key constraints
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

            $table->foreign('book_id')
                  ->references('id')
                  ->on('books')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropForeign(['board_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['book_id']);
        });

        Schema::dropIfExists('chapters');
    }
};
