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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            // Explicit unsignedBigInteger for foreign keys
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('grade_id');

            $table->string('name');
            $table->string('code');
            $table->boolean('is_premium')->default(false);
            $table->string('avatar')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();

            // Official Laravel foreign key constraints
            $table->foreign('board_id')
                  ->references('id')
                  ->on('boards')
                  ->onDelete('cascade');

            $table->foreign('grade_id')
                  ->references('id')
                  ->on('grades')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first to avoid errors
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['board_id']);
            $table->dropForeign(['grade_id']);
        });

        Schema::dropIfExists('subjects');
    }
};
